<?php

namespace App\Command;

use App\Entity\Categorie;
use App\Entity\Microservice;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:generate-category',
    description: 'Add a short description for your command',
)]
class GenerateCategoryCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        $categories = $this->entityManager->getRepository(Categorie::class)->findBy(['id' => [11, 12, 13, 14, 15, 16]]);

        foreach ($categories as $category) {
            $slugger = new AsciiSlugger('fr_FR');

            $slug = $slugger->slug(strtolower($category->getName())  .'-' . time());
            $category->setSlug($slug);
        }

//        foreach ($users as $user) {
//            $rand_keys = array_rand($categories, 1);
//            $cat = $categories[$rand_keys];
//            $user->setCategorie($cat);
//
//            $io->success(sprintf('Microservice update : %s ', $user->getId()));
//        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
