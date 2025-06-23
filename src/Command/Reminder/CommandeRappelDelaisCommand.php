<?php

namespace App\Command\Reminder;

use App\Entity\Commande;
use App\Entity\Microservice;
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
    name: 'app:commande-rappel-delais',
    description: 'Rappeler le vendeur que ça commande proche de delais de livraison',
)]
class CommandeRappelDelaisCommand extends Command
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

        $commandes =  $this->entityManager->getRepository(Commande::class)->getCommandeApprocheDelails();

        foreach ($commandes as $commande) {
            if ($this->regardeSiLeDelaisMoins24H($commande)) {

                $io->info(
                    sprintf('[Rappel] Pour la commande "%s", email envoyé au vendeur !', $commande->getId())
                );

                $this->sendEmail($commande);
            }
        }

        $io->success('Terminé !');

        return Command::SUCCESS;
    }

    private function regardeSiLeDelaisMoins24H(Commande $commande): bool
    {
        $microService = $commande->getMicroservice();

        if (!$microService) {
            throw new \Exception(
                sprintf("Le microservice de la commande %s est introuvable !",  $commande->getId())
            );
        }

        $nombreJour = $microService->getNombreJour();

        foreach ($commande->getServiceOptions() as $serviceOption) {

            if (!$serviceOption->getDelai()) {
                continue;
            }

            $nombreJour += $serviceOption->getDelai();
        }

        if (!$nombreJour) {
            return false;
        }

        $reservationLimited = clone $commande->getReservationDate()->modify(
            sprintf('+%d days', $nombreJour - 1)
        );

        return $reservationLimited->format('Y-m-d')  == (new \DateTime())->format('Y-m-d');
    }

    private function sendEmail(Commande $commande)
    {
        /** Envoie du mail au client */
        $this->mailerService->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getVendeur()->getEmail(),
            "Rappel – Échéance de livraison pour la prestation ⏳",
            'mails/vendeur/delai_critique.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );
    }

}
