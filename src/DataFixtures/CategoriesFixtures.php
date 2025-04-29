<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class CategoriesFixtures extends Fixture
{
    private $sluger;

    public function __construct(SluggerInterface $sluger)
    {
        $this->sluger = $sluger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $categories = [
            1 => [
                'name' => 'Studio',
            ],
            2 => [
                'name' => 'Vidéaste',
            ],
            3 => [
                'name' => 'Ingénieur son',
            ],
            4 => [
                'name' => 'Figurant Danseur',
            ],
            5 => [
                'name' => 'Photographe',
            ],
        ];

        foreach($categories as $key => $value){

            $categorie = new Categorie();
            
            $categorie->setName($value['name']);
            $categorie->setSlug(strtolower($this->sluger->slug($value['name'])));
            $categorie->setHexColor($faker->hexColor());
            $manager->persist($categorie);

            // Enregistre la catégorie dans une référence
            $this->addReference('categorie_' . $key, $categorie);
        }

        $manager->flush();
    }
}
