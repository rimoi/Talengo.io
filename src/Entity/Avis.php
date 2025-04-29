<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\AvisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Avis
{
    use Timestamp;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $contenu;

    #[ORM\Column(type: 'string', length: 255)]
    private $type;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $vendeur;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $client;

    #[ORM\ManyToOne(targetEntity: Microservice::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $microservice;

    #[ORM\OneToMany(mappedBy: 'avis', targetEntity: Commande::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $commandes;

    #[ORM\OneToMany(mappedBy: 'avis', targetEntity: AvisReponse::class)]
    private Collection $avisReponses;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->avisReponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

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

    public function getMicroservice(): ?Microservice
    {
        return $this->microservice;
    }

    public function setMicroservice(?Microservice $microservice): self
    {
        $this->microservice = $microservice;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
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
            $commande->setAvis($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getAvis() === $this) {
                $commande->setAvis(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getContenu();
    }

    /**
     * @return Collection<int, AvisReponse>
     */
    public function getAvisReponses(): Collection
    {
        return $this->avisReponses;
    }

    public function addAvisReponse(AvisReponse $avisReponse): self
    {
        if (!$this->avisReponses->contains($avisReponse)) {
            $this->avisReponses->add($avisReponse);
            $avisReponse->setAvis($this);
        }

        return $this;
    }

    public function removeAvisReponse(AvisReponse $avisReponse): self
    {
        if ($this->avisReponses->removeElement($avisReponse)) {
            // set the owning side to null (unless already changed)
            if ($avisReponse->getAvis() === $this) {
                $avisReponse->setAvis(null);
            }
        }

        return $this;
    }
}
