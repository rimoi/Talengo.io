<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Commande
{
    use Timestamp;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $offre;

    #[ORM\Column(type: 'float')]
    private $montant;

    #[ORM\ManyToOne(targetEntity: Microservice::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $microservice;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    private $client;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private $vendeur;

    #[ORM\Column(type: 'boolean')]
    private $validate;

    #[ORM\Column(type: 'boolean')]
    private $deliver;

    #[ORM\Column(type: 'boolean')]
    private $cancel;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deliverAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $cancelAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $validateAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $statut;

    #[ORM\Column(type: 'boolean')]
    private $confirmationClient;

    #[ORM\Column(type: 'boolean')]
    private $lu;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'destinatairesCommandes')]
    private $destinataire;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Message::class)]
    private $messages;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeMessage::class)]
    private $commandeMessages;

    #[ORM\Column(nullable: true)]
    private ?bool $rapportValidate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $rapportValidateAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Rapport $rapport = null;

    #[ORM\ManyToMany(targetEntity: ServiceOption::class, inversedBy: 'commandes')]
    private Collection $serviceOptions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $payment_intent = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Remboursement::class)]
    private Collection $remboursements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $disponibilite = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Offre $pack = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Avis $avis = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $reservationDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reservationStartAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reservationEndAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $payed = null;

    #[ORM\Column(nullable: true)]
    private ?int $tauxHoraire = null;

    #[ORM\Column(nullable: true)]
    private ?int $startHoure = null;

    #[ORM\Column(nullable: true)]
    private ?int $endHoure = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->commandeMessages = new ArrayCollection();
        $this->serviceOptions = new ArrayCollection();
        $this->remboursements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOffre(): ?string
    {
        return $this->offre;
    }

    public function setOffre(?string $offre): self
    {
        $this->offre = $offre;

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

    public function getMicroservice(): ?Microservice
    {
        return $this->microservice;
    }

    public function setMicroservice(?Microservice $microservice): self
    {
        $this->microservice = $microservice;

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

    public function getVendeur(): ?User
    {
        return $this->vendeur;
    }

    public function setVendeur(?User $vendeur): self
    {
        $this->vendeur = $vendeur;

        return $this;
    }

    public function getValidate(): ?bool
    {
        return $this->validate;
    }

    public function setValidate(bool $validate): self
    {
        $this->validate = $validate;

        return $this;
    }

    public function getDeliver(): ?bool
    {
        return $this->deliver;
    }

    public function setDeliver(bool $deliver): self
    {
        $this->deliver = $deliver;

        return $this;
    }

    public function getCancel(): ?bool
    {
        return $this->cancel;
    }

    public function setCancel(bool $cancel): self
    {
        $this->cancel = $cancel;

        return $this;
    }

    public function getDeliverAt(): ?\DateTimeInterface
    {
        return $this->deliverAt;
    }

    public function setDeliverAt(?\DateTimeInterface $deliverAt): self
    {
        $this->deliverAt = $deliverAt;

        return $this;
    }

    public function getCancelAt(): ?\DateTimeInterface
    {
        return $this->cancelAt;
    }

    public function setCancelAt(?\DateTimeInterface $cancelAt): self
    {
        $this->cancelAt = $cancelAt;

        return $this;
    }

    public function getValidateAt(): ?\DateTimeInterface
    {
        return $this->validateAt;
    }

    public function setValidateAt(?\DateTimeInterface $validateAt): self
    {
        $this->validateAt = $validateAt;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getConfirmationClient(): ?bool
    {
        return $this->confirmationClient;
    }

    public function setConfirmationClient(bool $confirmationClient): self
    {
        $this->confirmationClient = $confirmationClient;

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

    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(?User $destinataire): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setCommande($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getCommande() === $this) {
                $message->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CommandeMessage[]
     */
    public function getCommandeMessages(): Collection
    {
        return $this->commandeMessages;
    }

    public function addCommandeMessage(CommandeMessage $commandeMessage): self
    {
        if (!$this->commandeMessages->contains($commandeMessage)) {
            $this->commandeMessages[] = $commandeMessage;
            $commandeMessage->setCommande($this);
        }

        return $this;
    }

    public function removeCommandeMessage(CommandeMessage $commandeMessage): self
    {
        if ($this->commandeMessages->removeElement($commandeMessage)) {
            // set the owning side to null (unless already changed)
            if ($commandeMessage->getCommande() === $this) {
                $commandeMessage->setCommande(null);
            }
        }

        return $this;
    }

    public function isRapportValidate(): ?bool
    {
        return $this->rapportValidate;
    }

    public function setRapportValidate(?bool $rapportValidate): self
    {
        $this->rapportValidate = $rapportValidate;

        return $this;
    }

    public function getRapportValidateAt(): ?\DateTimeInterface
    {
        return $this->rapportValidateAt;
    }

    public function setRapportValidateAt(?\DateTimeInterface $rapportValidateAt): self
    {
        $this->rapportValidateAt = $rapportValidateAt;

        return $this;
    }

    public function getRapport(): ?Rapport
    {
        return $this->rapport;
    }

    public function setRapport(?Rapport $rapport): self
    {
        $this->rapport = $rapport;

        return $this;
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
        }

        return $this;
    }

    public function removeServiceOption(ServiceOption $serviceOption): self
    {
        $this->serviceOptions->removeElement($serviceOption);

        return $this;
    }

    public function getPaymentIntent(): ?string
    {
        return $this->payment_intent;
    }

    public function setPaymentIntent(?string $payment_intent): self
    {
        $this->payment_intent = $payment_intent;

        return $this;
    }

    /**
     * @return Collection<int, Remboursement>
     */
    public function getRemboursements(): Collection
    {
        return $this->remboursements;
    }

    public function addRemboursement(Remboursement $remboursement): self
    {
        if (!$this->remboursements->contains($remboursement)) {
            $this->remboursements->add($remboursement);
            $remboursement->setCommande($this);
        }

        return $this;
    }

    public function removeRemboursement(Remboursement $remboursement): self
    {
        if ($this->remboursements->removeElement($remboursement)) {
            // set the owning side to null (unless already changed)
            if ($remboursement->getCommande() === $this) {
                $remboursement->setCommande(null);
            }
        }

        return $this;
    }

    public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(?string $disponibilite): self
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    public function getPack(): ?Offre
    {
        return $this->pack;
    }

    public function setPack(?Offre $pack): self
    {
        $this->pack = $pack;

        return $this;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): self
    {
        $this->avis = $avis;

        return $this;
    }

    public function getReservationDate(): ?\DateTimeInterface
    {
        return $this->reservationDate;
    }

    public function setReservationDate(?\DateTimeInterface $reservationDate): self
    {
        $this->reservationDate = $reservationDate;

        return $this;
    }

    public function getReservationStartAt(): ?\DateTimeInterface
    {
        return $this->reservationStartAt;
    }

    public function setReservationStartAt(?\DateTimeInterface $reservationStartAt): self
    {
        $this->reservationStartAt = $reservationStartAt;

        return $this;
    }

    public function getReservationEndAt(): ?\DateTimeInterface
    {
        return $this->reservationEndAt;
    }

    public function setReservationEndAt(?\DateTimeInterface $reservationEndAt): self
    {
        $this->reservationEndAt = $reservationEndAt;

        return $this;
    }

    public function isPayed(): ?bool
    {
        return $this->payed;
    }

    public function setPayed(?bool $payed): self
    {
        $this->payed = $payed;

        return $this;
    }

    public function getTauxHoraire(): ?int
    {
        return $this->tauxHoraire;
    }

    public function setTauxHoraire(?int $tauxHoraire): self
    {
        $this->tauxHoraire = $tauxHoraire;

        return $this;
    }

    public function getStartHoure(): ?int
    {
        return $this->startHoure;
    }

    public function setStartHoure(?int $startHoure): self
    {
        $this->startHoure = $startHoure;

        return $this;
    }

    public function getEndHoure(): ?int
    {
        return $this->endHoure;
    }

    public function setEndHoure(?int $endHoure): self
    {
        $this->endHoure = $endHoure;

        return $this;
    }
}
