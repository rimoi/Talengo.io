<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\ServiceOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceOptionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ServiceOption
{
    use Timestamp;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $delai = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\ManyToMany(targetEntity: Microservice::class, inversedBy: 'serviceOptions')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $microservice;

    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'serviceOptions')]
    private Collection $commandes;

    public function __construct()
    {
        $this->microservice = new ArrayCollection();
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDelai(): ?int
    {
        return $this->delai;
    }

    public function setDelai(?int $delai): self
    {
        $this->delai = $delai;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * @return Collection<int, Microservice>
     */
    public function getMicroservice(): Collection
    {
        return $this->microservice;
    }

    public function addMicroservice(Microservice $microservice): self
    {
        if (!$this->microservice->contains($microservice)) {
            $this->microservice->add($microservice);
        }

        return $this;
    }

    public function removeMicroservice(Microservice $microservice): self
    {
        $this->microservice->removeElement($microservice);

        return $this;
    }

    public function __toString()
    {
        return $this->getName() . ' - ' . $this->getMontant() . '$ ( + ' . $this->getDelai() . ' jours de réalisation)';
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->addServiceOption($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            $commande->removeServiceOption($this);
        }

        return $this;
    }
}
