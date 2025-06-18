<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
/**
 * @Vich\Uploadable
 */
class Message
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

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    /**
     * @Groups("message")
     */
    private $conversation;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    /**
     * @Groups("message")
     */
    private $auteur;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    /**
     * @Groups("message")
     */
    private $destinataire;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private $lu;

    #[ORM\OneToMany(mappedBy: 'lastMessage', targetEntity: Conversation::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $conversations;

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $commande;

    /**
     * @Vich\UploadableField(mapping="messages_files", fileNameProperty="file")
     * @var File|null
    **/
    private $messageFile;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    public function getUser(): ?User
    {
        return $this->auteur;
    }

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
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

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(?User $destinataire): self
    {
        $this->destinataire = $destinataire;

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

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->setLastMessage($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getLastMessage() === $this) {
                $conversation->setLastMessage(null);
            }
        }

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
