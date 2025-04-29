<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\PortefeuilleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortefeuilleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Portefeuille
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float')]
    private $soldeDisponible;

    #[ORM\Column(type: 'float', nullable: true)]
    private $soldeEnCours;

    #[ORM\OneToOne(inversedBy: 'portefeuille', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $vendeur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoldeDisponible(): ?float
    {
        return $this->soldeDisponible;
    }

    public function setSoldeDisponible(float $soldeDisponible): self
    {
        $this->soldeDisponible = $soldeDisponible;

        return $this;
    }

    public function getSoldeEnCours(): ?float
    {
        return $this->soldeEnCours;
    }

    public function setSoldeEnCours(?float $soldeEnCours): self
    {
        $this->soldeEnCours = $soldeEnCours;

        return $this;
    }

    public function getVendeur(): ?User
    {
        return $this->vendeur;
    }

    public function setVendeur(?User $vendeur): self
    {
        $this->vendeur = $vendeur;

        return $this;
    }
}
