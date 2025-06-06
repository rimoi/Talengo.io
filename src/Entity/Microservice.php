<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\MicroserviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MicroserviceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Microservice
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'microservices')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $vendeur;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $online;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: Commande::class)]
    private $commandes;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: Avis::class)]
    private $avis;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: Favoris::class)]
    private $favoris;

    #[ORM\ManyToMany(targetEntity: ServiceOption::class, mappedBy: 'microservice', cascade: ["persist"])]
    private Collection $serviceOptions;

    #[ORM\Column(nullable: true)]
    private ?int $delai = null;

    #[ORM\Column]
    private ?float $prixMastering = null;

    #[ORM\Column]
    private ?float $prixMixage = null;

    #[ORM\Column]
    private ?float $prixBeatmaking = null;

    #[ORM\Column]
    private ?bool $promo = null;

    #[ORM\ManyToOne(inversedBy: 'microservices')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Categorie $categorie = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixComposition = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?int $numberAvis = null;

    // Nombre de jour pour la réalisation
    #[ORM\Column(nullable: true)]
    private ?int $nombreJour = null;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: Media::class, cascade: ["persist"])]
    private Collection $medias;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: ServiceSignale::class, cascade: ["persist"])]
    private Collection $serviceSignales;

    #[ORM\ManyToMany(targetEntity: EmploisTemps::class, inversedBy: 'microservices', cascade: ["persist"])]
    private Collection $emploitemps;

    #[ORM\Column]
    private ?bool $offline = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Disponibilite::class, cascade: ["persist"])]
    private Collection $disponibilites;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: Realisation::class, cascade: ["persist"])]
    private Collection $realisations;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: Tag::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'microservice', targetEntity: FAQ::class, cascade: ["persist"])]
    private Collection $faq;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->serviceOptions = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->serviceSignales = new ArrayCollection();
        $this->emploitemps = new ArrayCollection();
        $this->disponibilites = new ArrayCollection();
        $this->realisations = new ArrayCollection();
        $this->tag = new ArrayCollection();
        $this->faq = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(?bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|Commande[]
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->setMicroservice($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getMicroservice() === $this) {
                $commande->setMicroservice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Avis[]
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
            $avi->setMicroservice($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getMicroservice() === $this) {
                $avi->setMicroservice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Favoris[]
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori): self
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris[] = $favori;
            $favori->setMicroservice($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): self
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getMicroservice() === $this) {
                $favori->setMicroservice(null);
            }
        }

        return $this;
    }

    /**
     * Verifi si ce microcervice a déjà ajouter en favoris par un utilisateur
     *
     * @param User $user
     * @return boolean
     */
    public function isAddedByUser(User $user){
        foreach($this->favoris as $favori){
            if($favori->getClient() === $user) return true;
        }

        return false;
    }

    /**
     * @return Collection<int, ServiceOption>
     */
    public function getServiceOptions(): Collection
    {
        return $this->serviceOptions;
    }

    public function addServiceOption(ServiceOption $serviceOption): self
    {
        if (!$this->serviceOptions->contains($serviceOption)) {
            $this->serviceOptions->add($serviceOption);
            $serviceOption->addMicroservice($this);
        }

        return $this;
    }

    public function removeServiceOption(ServiceOption $serviceOption): self
    {
        if ($this->serviceOptions->removeElement($serviceOption)) {
            $serviceOption->removeMicroservice($this);
        }

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

    public function getPrixMastering(): ?float
    {
        return $this->prixMastering;
    }

    public function setPrixMastering(float $prixMastering): self
    {
        $this->prixMastering = $prixMastering;

        return $this;
    }

    public function getPrixMixage(): ?float
    {
        return $this->prixMixage;
    }

    public function setPrixMixage(float $prixMixage): self
    {
        $this->prixMixage = $prixMixage;

        return $this;
    }

    public function getPrixBeatmaking(): ?float
    {
        return $this->prixBeatmaking;
    }

    public function setPrixBeatmaking(float $prixBeatmaking): self
    {
        $this->prixBeatmaking = $prixBeatmaking;

        return $this;
    }

    public function isPromo(): ?bool
    {
        return $this->promo;
    }

    public function setPromo(bool $promo): self
    {
        $this->promo = $promo;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getPrixComposition(): ?float
    {
        return $this->prixComposition;
    }

    public function setPrixComposition(?float $prixComposition): self
    {
        $this->prixComposition = $prixComposition;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setMicroservice($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getMicroservice() === $this) {
                $media->setMicroservice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ServiceSignale>
     */
    public function getServiceSignales(): Collection
    {
        return $this->serviceSignales;
    }

    public function addServiceSignale(ServiceSignale $serviceSignale): self
    {
        if (!$this->serviceSignales->contains($serviceSignale)) {
            $this->serviceSignales->add($serviceSignale);
            $serviceSignale->setMicroservice($this);
        }

        return $this;
    }

    public function removeServiceSignale(ServiceSignale $serviceSignale): self
    {
        if ($this->serviceSignales->removeElement($serviceSignale)) {
            // set the owning side to null (unless already changed)
            if ($serviceSignale->getMicroservice() === $this) {
                $serviceSignale->setMicroservice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EmploisTemps>
     */
    public function getEmploitemps(): Collection
    {
        return $this->emploitemps;
    }

    public function addEmploitemp(EmploisTemps $emploitemp): self
    {
        if (!$this->emploitemps->contains($emploitemp)) {
            $this->emploitemps->add($emploitemp);
        }

        return $this;
    }

    public function removeEmploitemp(EmploisTemps $emploitemp): self
    {
        $this->emploitemps->removeElement($emploitemp);

        return $this;
    }

    public function getOffline(): ?bool
    {
        return $this->offline;
    }

    public function setOffline(bool $offline): self
    {
        $this->offline = $offline;

        return $this;
    }

    /**
     * @return Collection<int, Disponibilite>
     */
    public function getDisponibilites(): Collection
    {
        return $this->disponibilites;
    }

    public function addDisponibilite(Disponibilite $disponibilite): self
    {
        if (!$this->disponibilites->contains($disponibilite)) {
            $this->disponibilites->add($disponibilite);
            $disponibilite->setService($this);
        }

        return $this;
    }

    public function removeDisponibilite(Disponibilite $disponibilite): self
    {
        if ($this->disponibilites->removeElement($disponibilite)) {
            // set the owning side to null (unless already changed)
            if ($disponibilite->getService() === $this) {
                $disponibilite->setService(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Realisation>
     */
    public function getRealisations(): Collection
    {
        return $this->realisations;
    }

    public function addRealisation(Realisation $realisation): self
    {
        if (!$this->realisations->contains($realisation)) {
            $this->realisations->add($realisation);
            $realisation->setMicroservice($this);
        }

        return $this;
    }

    public function removeRealisation(Realisation $realisation): self
    {
        if ($this->realisations->removeElement($realisation)) {
            // set the owning side to null (unless already changed)
            if ($realisation->getMicroservice() === $this) {
                $realisation->setMicroservice(null);
            }
        }

        return $this;
    }

    public function getNumberAvis(): ?int
    {
        return $this->numberAvis;
    }

    public function incrementAvis(): self
    {
        if ($this->numberAvis === null) {
            $this->numberAvis = 0;
        }

        ++$this->numberAvis;

        return $this;
    }

    public function getNombreJour(): ?int
    {
        return $this->nombreJour;
    }

    public function setNombreJour(?int $nombreJour): void
    {
        $this->nombreJour = $nombreJour;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setMicroservice($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getMicroservice() == $this) {
                $tag->setMicroservice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FAQ>
     */
    public function getFaq(): Collection
    {
        return $this->faq;
    }

    public function addFaq(FAQ $faq): self
    {
        if (!$this->faq->contains($faq)) {
            $this->faq->add($faq);
            $faq->setMicroservice($this);
        }

        return $this;
    }

    public function removeFaq(FAQ $faq): self
    {
        if ($this->faq->removeElement($faq)) {
            // set the owning side to null (unless already changed)
            if ($faq->getMicroservice() === $this) {
                $faq->setMicroservice(null);
            }
        }

        return $this;
    }
}
