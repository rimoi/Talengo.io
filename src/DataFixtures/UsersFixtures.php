<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UsersFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($nbUsers = 1; $nbUsers <= 50; $nbUsers++) {
            $user = new User();
            if ($nbUsers == 1) {

                $user->setEmail('admin@gmail.com');
                $user->setRoles(['ROLE_ADMIN']);
                $user->setCompte('admin');

            } else {

                $user->setCompte($faker->randomElement(['Vendeur', 'Client']));

                if ($user->getCompte() == 'Client') {

                    $user->setRoles(['ROLE_CLIENT']);

                } elseif($user->getCompte() == 'Vendeur') {

                    $user->setRoles(['ROLE_VENDEUR']);
                }

                $user->setEmail($faker->email);
            }

            $user->setPrenom($faker->firstName());
            $user->setNom($faker->lastName());
            $user->setPseudo($faker->userName());
            $user->setUsePseudo($faker->numberBetween(0, 1));
            $user->setGenre($faker->randomElement(['Masculin', 'Feminin']));
            $user->setNameUrl($faker->firstName() . '-' . $faker->lastName());
            $user->setApropos($faker->realText(150));
            $user->setVille($faker->city);
            $user->setIsVerified(true);
            //$user->setIsLocked(false);
            $user->setPassword($this->encoder->hashPassword($user, 'azerty'));
            $manager->persist($user);

            // Enregistre l'utilisateur dans une référence
            $this->addReference('user_' . $nbUsers, $user);
        }

        $manager->flush();
    }
}
