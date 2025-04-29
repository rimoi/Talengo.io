<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class SearchUser
{

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $email = null;

    /**
     * @var string
     */
    public $genre = null;

    /**
     * @var string
     */
    public $compte = null;

    /**
     * @var string
     */
    public $ville = null;

    /**
     * @var ArrayCollection
     */
    public $categories;

    /**
     * @var int
     */
    public $isVerified = null;

    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of email
     *
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param  string  $email
     *
     * @return  self
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of genre
     *
     * @return  string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of genre
     *
     * @param  string  $genre
     *
     * @return  self
     */
    public function setGenre(?string $genre)
    {
        $this->genre = $genre;

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
     * Get the value of compte
     *
     * @return  string
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set the value of compte
     *
     * @param  string  $compte
     *
     * @return  self
     */
    public function setCompte(string $compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get the value of isVerified
     *
     * @return  int
     */ 
    public function getIsVerified()
    {
        return $this->isVerified;
    }

    /**
     * Set the value of isVerified
     *
     * @param  int  $isVerified
     *
     * @return  self
     */ 
    public function setIsVerified(?int $isVerified)
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
