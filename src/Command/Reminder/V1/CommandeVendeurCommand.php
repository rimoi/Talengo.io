<?php

namespace App\Command\Reminder\V1;

use App\Entity\Commande;
use App\Entity\Remboursement;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Omnipay\Omnipay;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:commande-vendeur',
    description: "
        - Un rappel 24h avant pour lui dire que le client a passer commande et qu'il doit valider la commande;
        - à la 48 annuler la commande et procéder a un remboursement au client si le vendeur na pas réagit
    ",
)]
class CommandeVendeurCommand extends Command
{
    private $privateKey;

    private $paypalkey;

    private array $errors = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService  $mailerService
    )
    {
        parent::__construct(null);

        $this->privateKey = $_ENV['STRIPE_SECRET_KEY'];

        $this->paypalkey = $_ENV['PAYPAL_SECRET'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $commandes =  $this->entityManager->getRepository(Commande::class)->getCommandePayer();

        foreach ($commandes as $commande) {

            // Ici c'est pour envoyer l'email pour rappler qu'il reste 24h pour que le vendeur puisse valider la commande.
            if ($this->regardeSiLeDelaisMoins24H($commande)) {

                $io->info(
                    sprintf('[Rappel] Pour la commande "%s", email envoyé au vendeur !', $commande->getId())
                );

                $this->sendEmail($commande);
            }

            // ICI c'est pour passer la commande en annuler si le délais impartie
            if ($this->regardeSiLeDelaisMoins24H($commande, true)) {

                $io->info(
                    sprintf('[Annuler] Pour la commande "%s", email envoyé au vendeur !', $commande->getId())
                );

                $this->annulerLaCommande($commande);
                $this->sendEmailTerminerCommande($commande);
            }
        }

        $io->success('Terminé !');

        return Command::SUCCESS;
    }

    private function regardeSiLeDelaisMoins24H(Commande $commande, bool $comparaison = false): bool
    {

        if ($comparaison) {

            $otherDate = clone $commande->getReservationDate();

            $otherDate->modify(
                sprintf('+%d days', 2)
            );

            $date = $otherDate->format('Y-m-d');

            unset($otherDate);

            return $date <= (new \DateTime())->format('Y-m-d');
        }

        $dateFin = clone $commande->getReservationDate();

        $dateFin->modify(
            sprintf('+%d days', 1)
        );

        $dateFinal = $dateFin->format('Y-m-d');

        unset($dateFin);

        return $dateFinal === (new \DateTime())->format('Y-m-d');
    }

    private function sendEmail(Commande $commande): void
    {
        /** Envoie du mail au vendeur */
        $this->mailerService->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getVendeur()->getEmail(),
            sprintf("Action requise – commande #%s en attente d’acceptation", $commande->getId()),
            'mails/cron/vendeur/_commande_reserver_24h.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );
    }

    private function sendEmailTerminerCommande(Commande $commande): void
    {
        /** Envoie du mail au client */
        $this->mailerService->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getClient()->getEmail(),
            sprintf("Annulation automatique - Commande #%s non traitée",  $commande->getId()),
            'mails/cron/client/_commande_client_annuler.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande,
            'talengo.contact@gmail.com'
        );

        /** Envoie du mail au vendeur */
        $this->mailerService->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getVendeur()->getEmail(),
            sprintf("Annulation automatique - Commande #%s non traitée",  $commande->getId()),
            'mails/cron/vendeur/_commande_annuler.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );
    }

    private function annulerLaCommande(Commande $commande)
    {
        $entityManager = $this->entityManager;

        if ($commande->isIsPayWithStripe()) {
            $gateway = Omnipay::create('Stripe');
            $gateway->setApiKey($this->privateKey);

            $response = $gateway->refund([
                'transactionReference' => $commande->getReferenceStripeId(),
                'amount' => $commande->getMontant(), // Optionnel, pour remboursement partiel
            ])->send();

            if ($response->isSuccessful()) {
                // Remboursement OK
                $refundId = $response->getTransactionReference();
                $commande->setReferenceStripeRefundId($refundId);

                $commande->setStatut('Annulé');

                $commande->setIsPayWithStripe(null);
            } else {

                $commande->setStripeErrorRefund('[BATCH] '.$response->getMessage());

                $entityManager->flush();

                $this->errors[] = $response->getMessage();

                return null;
            }
        } elseif ($commande->isIsPayWithStripe() === false) {
            $gateway = Omnipay::create('PayPal_Rest');
            $gateway->setClientId($_ENV['PAYPAL_CLIENT_ID']);
            $gateway->setSecret($_ENV['PAYPAL_SECRET']);

            if ($_ENV['APP_ENV'] === 'dev') {
                $gateway->setTestMode(true);
            }

            $response = $gateway->refund([
                'transactionReference' => $commande->getReferencePaypalId(),
                'amount' => number_format($commande->getMontant(), 2, '.', ''),
                'currency' => 'EUR',
            ])->send();

            if ($response->isSuccessful()) {
                $refundData = $response->getData();

                $commande->setReferencePaypalRefundId($refundData['id']);

                $commande->setStatut('Annulé');

                $commande->setIsPayWithStripe(null);
            } else {

                $commande->setPaypalErrorRefund('[BATCH] ' . $response->getMessage());

                $entityManager->flush();

                $this->errors[] = $response->getMessage();

            }

        } else {
            $this->errors[] = "Impossible d'identifier la méthode de paiement utilisé !";
        }

        $remboursement = new Remboursement();
        $remboursement->setUser($commande->getClient());
        $remboursement->setVendeur($commande->getVendeur());
        $remboursement->setCommande($commande);
        $remboursement->setMontant($commande->realPriceWithOutFee());
        $remboursement->setMotif("Commande annulée par le prestataire");
        $remboursement->setStatut("Annuler");
        $entityManager->persist($remboursement);

        $portefeuille = $commande->getVendeur()->getPortefeuille();

        if ($commande->realPriceWithOutFee() >= $portefeuille->getSoldeEncours()) {

            $difference = $commande->realPriceWithOutFee() - $portefeuille->getSoldeEncours();
        } else {

            $difference = $portefeuille->getSoldeEncours() - $commande->realPriceWithOutFee();
        }

        $portefeuille->setSoldeEncours($difference);

        $commande->setLu(false);
        $commande->setDestinataire($commande->getClient());

        $commande->setCancel(true);
        $commande->setStatut('Annuler');
        $commande->setDeliver(false);
        $commande->setValidate(false);
        $commande->setCancelAt(new \DateTimeImmutable());

        $entityManager->flush();
    }
}
