<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\EmploisTempsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmploisTempsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmploisTemps
{
    use Timestamp;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureOuverture = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureCloture = null;

    #[ORM\ManyToOne(inversedBy: 'emploisTemps')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?User $vendeur = null;

    #[ORM\ManyToMany(targetEntity: Microservice::class, mappedBy: 'emploitemps')]
    private Collection $microservices;

    #[ORM\Column(type: 'array', nullable: true)]
    private $jours = [];

    public function __construct()
    {
        $this->microservices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getHeureOuverture(): ?\DateTimeInterface
    {
        return $this->heureOuverture;
    }

    public function setHeureOuverture(\DateTimeInterface $heureOuverture): self
    {
        $this->heureOuverture = $heureOuverture;

        return $this;
    }

    public function getHeureCloture(): ?\DateTimeInterface
    {
        return $this->heureCloture;
    }

    public function setHeureCloture(\DateTimeInterface $heureCloture): self
    {
        $this->heureCloture = $heureCloture;

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

    /**
     * @return Collection<int, Microservice>
     */
    public function getMicroservices(): Collection
    {
        return $this->microservices;
    }

    public function addMicroservice(Microservice $microservice): self
    {
        if (!$this->microservices->contains($microservice)) {
            $this->microservices->add($microservice);
            $microservice->addEmploitemp($this);
        }

        return $this;
    }

    public function removeMicroservice(Microservice $microservice): self
    {
        if ($this->microservices->removeElement($microservice)) {
            $microservice->removeEmploitemp($this);
        }

        return $this;
    }

    public function getJours()
    {
        return $this->jours;
    }

    public function setJours(?array $jours): self
    {
        $this->jours = $jours;

        return $this;
    }
}
