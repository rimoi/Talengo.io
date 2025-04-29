<?php

namespace App\DataFixtures;

use App\Entity\Microservice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;


class MicroservicesFixtures extends Fixture implements DependentFixtureInterface
{
    private $sluger;

    public function __construct(SluggerInterface $sluger)
    {
        $this->sluger = $sluger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($microservices = 1; $microservices <= 500; $microservices++){
            $user = $this->getReference('user_'. $faker->numberBetween(1, 50));
            $categories = $this->getReference('categorie_'. $faker->numberBetween(1, 5));

            $microservice = new Microservice();

            $microservice->setName($faker->realText(50));
            $microservice->setDescription($faker->realText(500));
            $microservice->setDelai($faker->numberBetween(1, 30));
            $microservice->setCategorie($categories);

            /*foreach($categories as $key => $value){

                $microservice->setCategorie($value['name']);
            }

            $microservice->setBasiquePrice($faker->numberBetween(50, 100));
            $microservice->setStandardPrice($faker->numberBetween(200, 500));
            $microservice->setPremiumPrice($faker->numberBetween(600, 10000));
            $microservice->setPromo($faker->numberBetween(0, 1));

            $microservice->setPremiumDescription($faker->realText(100));
            $microservice->setDeleted(false);*/

            $microservice->setSlug($this->sluger->slug($faker->realText(150)));
            $microservice->setOnline($faker->numberBetween(0, 1));
            if ($user->getCompte() == 'Vendeur') {
                
            }
            $microservice->setVendeur($user);
            $microservice->setPromo($faker->numberBetween(0, 1));
            $microservice->setOffline($faker->numberBetween(0, 1));
            $microservice->setPrixMastering($faker->numberBetween(0, 1000));
            $microservice->setPrixMixage($faker->numberBetween(0, 1000));
            $microservice->setPrixBeatmaking($faker->numberBetween(0, 1000));
            $microservice->setPrixComposition($faker->numberBetween(0, 1000));
            $microservice->setPrix($faker->numberBetween(0, 1000));

            $manager->persist($microservice);

            // Enregistre l'utilisateur dans une référence
            $this->addReference('microservice_'. $microservices, $microservice);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UsersFixtures::class,
            CategoriesFixtures::class,
        ];
    }
}

