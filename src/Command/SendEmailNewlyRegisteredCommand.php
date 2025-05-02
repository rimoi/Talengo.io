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
    name: 'app:send-email-newly-registered',
    description: 'Nouveau inscrit',
)]
class SendEmailNewlyRegisteredCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ){
        parent::__construct(null);
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->em->getRepository(User::class)->getNewlyRegistred();

        foreach ($users as $user)
        {
            $templateEmail = (new TemplatedEmail())
                ->from(new Address('talengo.contact@gmail.com', 'Talengo.io'))
                ->to(new Address($user->getEmail()))
                ->subject('Bienvenue !')
                ->htmlTemplate('mails/_newly_registred.html.twig')
                ->context([]);

            $this->mailer->send($templateEmail);
        }

        $io->success(sprintf('Emails envoyés à : %s Utilisateurs', count($users)));

        return Command::SUCCESS;
    }
}
