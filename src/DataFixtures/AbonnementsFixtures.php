<?php

namespace App\DataFixtures;

use App\Entity\Abonnement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AbonnementsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        /*for($abonnements = 1; $abonnements <= 3; $abonnements++){
            $user = $this->getReference('user_'. $faker->numberBetween(1, 50));
            $stripe = $this->getReference('stripe_'. $faker->numberBetween(1, 3));

            $abonnement = new Abonnement();

            if ($user->getCompte() == 'Vendeur') {

                if ($abonnement->getUser() == $user) {

                }else{
                    
                    $abonnement->setUser($user);
                    $abonnement->setStripe($stripe);
                    $abonnement->setStatut(true);
                    $manager->persist($abonnement);

                    // Enregistre l'utilisateur dans une référence
                    $this->addReference('abonnement_'. $abonnements, $abonnement);
                }
            }
        }

        $manager->flush();*/
    }

    public function getDependencies()
    {
        return [
            UsersFixtures::class,
            StripeFixtures::class,
        ];
    }
}
