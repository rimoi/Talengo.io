<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\AbonnementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Abonnement
{
    use Timestamp;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Stripe::class, inversedBy: 'abonnements')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $stripe;

    #[ORM\OneToOne(inversedBy: 'abonnement', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $user;

    #[ORM\Column(type: 'boolean')]
    private $statut;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCanceled = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStripe(): ?Stripe
    {
        return $this->stripe;
    }

    public function setStripe(?Stripe $stripe): self
    {
        $this->stripe = $stripe;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isIsCanceled(): ?bool
    {
        return $this->isCanceled;
    }

    public function setIsCanceled(?bool $isCanceled): self
    {
        $this->isCanceled = $isCanceled;

        return $this;
    }
}
