<?php

namespace App\Command\Reminder\V1;

use App\Entity\Commande;
use App\Entity\Microservice;
use App\Entity\Retouche;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:commande-client',
    description: "
        - Un rappel 48h avant la clôture de la commande si le client ne réagit pas dans les 7 jours ouvrables;
        - Au bout de 7 jours ouvrables je cloture la commande si le client ne réagit pas en passant la commande au retouche ou livré
    ",
)]
class CommandeClientCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService  $mailerService
    )
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $commandes =  $this->entityManager->getRepository(Commande::class)->getCommandeClientEncours();

        foreach ($commandes as $commande) {

            // ICI c'est pour envoyer l'email pour rappler qu'il reste 48h avant pour que le client puisse valider la commande ou pas
            if ($this->regardeSiLeDelaisMoins24H($commande)) {

                $io->info(
                    sprintf('[Rappel] Pour la commande "%s", email envoyé au vendeur !', $commande->getId())
                );

                $this->sendEmail($commande);
            }

            // ICI c'est pour passer la commande en terminé si le délais impartie
            if ($this->regardeSiLeDelaisMoins24H($commande, true)) {

                $io->info(
                    sprintf('[Annuler] Pour la commande "%s", email envoyé au vendeur !', $commande->getId())
                );

                $this->fermerLaCommande($commande);
                $this->sendEmailTerminerCommande($commande);
            }
        }

        $io->success('Terminé !');

        return Command::SUCCESS;
    }

    private function regardeSiLeDelaisMoins24H(Commande $commande, bool $comparaison = false): bool
    {
        $microService = $commande->getMicroservice();

        if (!$microService) {
            return false;
        }

        if ($commande->isRetouche()) {
            return false;
        }

        /** @var Retouche $retouche */
        $retouche = $commande->getRetouches()->last();

        if ($retouche && !$retouche->getFinRetoucheDate()) {
            return false;
        }

        if (!$retouche) {

            if ($comparaison) {
                $otherDate = clone $commande->getDeliverAt();

                $otherDate->modify(
                    sprintf('+%d days', 7)
                );

                $date = $otherDate->format('Y-m-d');

                unset($otherDate);

                return $date <= (new \DateTime())->format('Y-m-d');
            }

            $dateFin = clone $commande->getDeliverAt();

            $dateFin->modify(
                sprintf('+%d days', 5)
            );

            $dateFinal = $dateFin->format('Y-m-d');

            unset($dateFin);

            return $dateFinal === (new \DateTime())->format('Y-m-d');
        }

        if ($comparaison) {

            $otherDate = clone $retouche->getFinRetoucheDate();

            $otherDate->modify(
                sprintf('+%d days', 7)
            );

            $date = $otherDate->format('Y-m-d');

            unset($otherDate);

            return $date <= (new \DateTime())->format('Y-m-d');
        }

        $dateFin = clone $retouche->getFinRetoucheDate();

        $dateFin->modify(
            sprintf('+%d days', 5)
        );

        $dateFinal = $dateFin->format('Y-m-d');

        unset($dateFin);

        return $dateFinal === (new \DateTime())->format('Y-m-d');
    }

    private function sendEmail(Commande $commande): void
    {
        /** Envoie du mail au client */
        $this->mailerService->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getClient()->getEmail(),
            "Votre commande sera clôturée sous 48h",
            'mails/cron/client/rappel_48h.html.twig',
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
            sprintf("Votre commande #[%s] a été clôturée automatiquement", $commande->getId()),
            'mails/cron/client/_commande_client_cloturer.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );
    }

    private function fermerLaCommande(Commande $commande): void
    {
        $commande->setCloturer(true);
        $commande->setCloturerDate(new \DateTime());

        $portefeuille = $commande->getVendeur()->getPortefeuille();

        $somme = $commande->realPriceWithOutFee() + $portefeuille->getSoldeDisponible();

        if ($commande->realPriceWithOutFee() >= $portefeuille->getSoldeEncours()) {

            $difference = $commande->realPriceWithOutFee() - $portefeuille->getSoldeEncours();
        } else {

            $difference = $portefeuille->getSoldeEncours() - $commande->realPriceWithOutFee();
        }

        $portefeuille->setSoldeDisponible($somme);
        $portefeuille->setSoldeEncours($difference);

        $commande->setDestinataire($commande->getVendeur());
        $commande->setLu(false);

        $this->entityManager->flush();

        /** Envoie du mail au vendeur */
        $this->mailerService->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getVendeur()->getEmail(),
            'Votre argent arrive !',
            'mails/_rapport_livrer.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );
    }
}
