<?php

namespace App\DataFixtures;

use App\Entity\Prix;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class PrixFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($microservices = 1; $microservices <= 400; $microservices++){
            $micoservice = $this->getReference('microservice_'. $faker->numberBetween(1, 200));

            $microservice = new Prix();

            $microservice->setName($faker->randomElement(['Basique', 'Standard']));
            $microservice->setTarif($faker->numberBetween(30, 200));
            $microservice->addMicroservice($micoservice);

            $manager->persist($microservice);

            // Enregistre l'utilisateur dans une référence
            $this->addReference('prix_'. $microservices, $microservice);
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
