<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Categorie;

class SearchService {

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var string
     */
    public $q = null;

    /**
     * @var ?string
     */
    public $ville;

    /**
     * @var string
     */
    public $jour = null;

    /**
     * @var time
     */
    public $heureOuverture = null;

    /**
     * @var time
     */
    public $heureCloture = null;

    /**
     * @var int|null
     */
    public $minPrice;

    /**
     * @var int|null
     */
    public $maxPrice;

    /**
     * @var bool|null
     */
    public $promo;

    /**
     * @var bool|null
     */
    public $online;

    /**
     * @var bool|null
     */
    public $offline;

    /**
     * @var ArrayCollection
     */
    public $categories;

    /**
     * @var ArrayCollection
     */
    public $villes;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories(): ArrayCollection
    {
        return $this->categories;
    }

    /**
     * @param ArrayCollection $categories
     */
    public function setCategories(ArrayCollection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * Set the value of q
     *
     * @param  string  $q
     *
     * @return  self
     */
    public function setQ(?string $q)
    {
        $this->q = $q;

        return $this;
    }

    /**
     * Get the value of ville
     *
     * @return  string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set the value of ville
     *
     * @param  string  $ville
     *
     * @return  self
     */
    public function setVille(?string $ville)
    {
        $this->ville = $ville;

        return $this;
    }
}