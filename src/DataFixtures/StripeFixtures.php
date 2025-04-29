<?php

namespace App\DataFixtures;

use App\Entity\Stripe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class StripeFixtures extends Fixture implements DependentFixtureInterface
{
    private $sluger;

    public function __construct(SluggerInterface $sluger)
    {
        $this->sluger = $sluger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $stripes = [
            1 => [
                'name' => 'offre de lancement',
                'tarif' => 5,99,
                'stripeKey' => 'price_1Ms830Kh8lE9zjqNe559KpR5',
            ],
        ];

        foreach($stripes as $key => $value){

            //$user = $this->getReference('user_'. $faker->numberBetween(1, 2));

            $stripe = new Stripe();
            $stripe->setName($value['name']);
            $stripe->setTarif($value['tarif']);
            $stripe->setStripeKey($value['stripeKey']);
            $stripe->setHexColor($faker->hexColor());
            $manager->persist($stripe);

            // Enregistre la catégorie dans une référence
            $this->addReference('stripe_' . $key, $stripe);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UsersFixtures::class
        ];
    }
}
