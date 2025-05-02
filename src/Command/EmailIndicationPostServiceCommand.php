<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:user-dont-have-microservice',
    description: "Récupèrer les utilisateurs qui n'ont pas encore publié une offre...",
)]
class EmailIndicationPostServiceCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    )
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->em->getRepository(User::class)->getUserNotHaveService();

        foreach ($users as $user)
        {
            $templateEmail = (new TemplatedEmail())
//                ->from(new Address('contact.lesextras@gmail.com', 'MISSION INFINITY'))
                ->from(new Address('sidilekhalifa1@gmail.com', 'MISSION INFINITY'))
//                ->to(new Address('sidi.khalifa@live.fr'))
                ->to(new Address($user->getEmail()))
                ->subject('Talengo.io - Bienvenue')
                ->htmlTemplate('mails/_valorisations.html.twig')
                ->context([
                    'nom' => $user->getNom()
                ]);

            $this->mailer->send($templateEmail);
        }
        $io->success(sprintf('Emails envoyés à : %s Utilisateurs', count($users)));

        return Command::SUCCESS;
    }
}
