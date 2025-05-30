<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\HasLifecycleCallbacks]
/**
 * @Vich\Uploadable
 */
class Categorie
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

    #[ORM\Column(type: 'string', length: 7)]
    private $hexColor;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Microservice::class)]
    private Collection $microservices;
    
    /**
     * @Vich\UploadableField(mapping="categories_images", fileNameProperty="image")
     * @var File|null
     * @Assert\Image(maxSize="10M", maxSizeMessage="Image trop volumineuse maximum 10Mb")
     * @Assert\Image(mimeTypes = {"image/jpeg", "image/jpg", "image/png"}, mimeTypesMessage = "Mauvais format d'image (jpeg, jpg et png)")
    **/
    private $imageFile;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;
    
    /**
     * @Vich\UploadableField(mapping="categories_icon_images", fileNameProperty="icone")
     * @var File|null
     * @Assert\Image(maxSize="10M", maxSizeMessage="Image trop volumineuse maximum 10Mb")
     * @Assert\Image(mimeTypes = {"image/jpeg", "image/jpg", "image/png"}, mimeTypesMessage = "Mauvais format d'image (jpeg, jpg et png)")
    **/
    private $iconeFile;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icone = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: User::class)]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Offre::class, mappedBy: 'categorie')]
    private Collection $offres;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    public function __construct()
    {
        $this->microservices = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->offres = new ArrayCollection();

        $slugger = new AsciiSlugger('fr_FR');

        $this->slug = $slugger->slug(strtolower($this->name)  .'-' . time());
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

    public function getHexColor(): ?string
    {
        return $this->hexColor;
    }

    public function setHexColor(string $hexColor): self
    {
        $this->hexColor = $hexColor;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
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
            $microservice->setCategorie($this);
        }

        return $this;
    }

    public function removeMicroservice(Microservice $microservice): self
    {
        if ($this->microservices->removeElement($microservice)) {
            // set the owning side to null (unless already changed)
            if ($microservice->getCategorie() === $this) {
                $microservice->setCategorie(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null eeFile
     */
    public function setIconeFile(?File $iconeFile = null): void
    {
        $this->iconeFile = $iconeFile;

        if (null !== $iconeFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getIconeFile(): ?File
    {
        return $this->iconeFile;
    }

    public function setIcone(?string $icone): self
    {
        $this->icone = $icone;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCategorie($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCategorie() === $this) {
                $user->setCategorie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): self
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->addCategorie($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): self
    {
        if ($this->offres->removeElement($offre)) {
            $offre->removeCategorie($this);
        }

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
