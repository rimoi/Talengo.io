<?php

namespace App\DataFixtures;

use App\Entity\Suivis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class SuivisFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        /*for ($suivis = 1; $suivis <= 300; $suivis++) {
            $user = $this->getReference('user_' . $faker->numberBetween(1, 300));

            $suivi = new Suivis();

            if ($user->getCompte() == 'Client') {

                $suivi->setClient($user);

                $manager->persist($suivi);

            }elseif($user->getCompte() == 'Vendeur'){

                $suivi->setVendeur($user);

                $manager->persist($suivi);

            }else{
                
                $suivi->setClient($user);

                $manager->persist($suivi);
            }

            // Enregistre l'utilisateur dans une référence
            $this->addReference('avis_' . $suivis, $suivi);
        }

        $manager->flush();*/
    }

    public function getDependencies()
    {
        return [
            UsersFixtures::class,
        ];
    }
}
