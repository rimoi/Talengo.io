<?php

namespace App\DataFixtures;

use App\Entity\ServiceOption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class MicroserviceOptionsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($microservices = 1; $microservices <= 1000; $microservices++){
            $micoservice = $this->getReference('microservice_'. $faker->numberBetween(1, 500));

            $microservice = new ServiceOption();

            $microservice->setName($faker->realText(50));
            $microservice->setMontant($faker->numberBetween(5, 100));
            $microservice->addMicroservice($micoservice);
            $microservice->setDelai($faker->numberBetween(1, 30));

            $manager->persist($microservice);

            // Enregistre l'utilisateur dans une référence
            $this->addReference('microserviceoption_'. $microservices, $microservice);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            MicroservicesFixtures::class,
        ];
    }
}
