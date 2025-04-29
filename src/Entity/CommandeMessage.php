<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\CommandeMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: CommandeMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
/**
 * @Vich\Uploadable
 */
class CommandeMessage
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /**
     * @Groups("message")
     */
    private $id;

    #[ORM\Column(type: 'text')]
    /**
     * @Groups("message")
     */
    private $contenu;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandeMessages')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    /**
     * @Groups("message")
     */
    private $user;

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'commandeMessages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    /**
     * @Groups("message")
     */
    private $commande;

    #[ORM\Column(type: 'boolean')]
    private $lu;

    /**
     * @Vich\UploadableField(mapping="messages_files", fileNameProperty="file")
     * @var File|null
    **/
    private $messageFile;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getLu(): ?bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): self
    {
        $this->lu = $lu;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $messageFile
     */
    public function setMessageFile(?File $messageFile = null): void
    {
        $this->messageFile = $messageFile;

        if (null !== $messageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getMessageFile(): ?File
    {
        return $this->messageFile;
    }
}
