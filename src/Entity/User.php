<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
/**
* @UniqueEntity(fields={"email"}, message="Cette adresse email est déjà utilisée pour un autre compte")
* @Vich\Uploadable
*/
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $nom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $prenom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $genre;

    /**
     * @Vich\UploadableField(mapping="users_avatars", fileNameProperty="avatar")
     * @var File|null
     * @Assert\Image(maxSize="2M", maxSizeMessage="Image trop volumineuse (maximum 2Mb)")
     * @Assert\Image(mimeTypes = {"image/jpeg", "image/jpg", "image/png"}, mimeTypesMessage = "Mauvais format d'image (jpeg, jpg et png)")
    **/
    private $imageFile;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $avatar;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    private $email;

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    private $compte;

    #[ORM\Column(type: 'text', nullable: true)]
    private $apropos;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(type: 'string', length: 255)]
    private $nameUrl;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profession = null;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: Microservice::class)]
    private $microservices;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $pays;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ville;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $adresse;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $telephone;

    #[ORM\OneToOne(mappedBy: 'vendeur', targetEntity: Portefeuille::class, cascade: ['persist', 'remove'])]
    private $portefeuille;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Abonnement::class, cascade: ['persist', 'remove'])]
    private $abonnement;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Commande::class)]
    private $commandes;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Avis::class)]
    private $avis;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Favoris::class)]
    private $favoris;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: Suivis::class)]
    private $suivis;

    #[ORM\OneToMany(mappedBy: 'user1', targetEntity: Conversation::class)]
    private $conversations;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Message::class)]
    private $messages;

    #[ORM\OneToMany(mappedBy: 'sendeur', targetEntity: Conversation::class)]
    private $senderconversations;

    #[ORM\OneToMany(mappedBy: 'destinataire', targetEntity: Commande::class)]
    private $destinatairesCommandes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CommandeMessage::class)]
    private $commandeMessages;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private $reseauAvatar;

    #[ORM\Column(nullable: true)]
    private ?int $facebookId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?string $longitude = null;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: Retrait::class)]
    private Collection $retraits;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: Realisation::class)]
    private Collection $realisations;

    /**
     * @Vich\UploadableField(mapping="users_couvertures", fileNameProperty="couverture")
     * @var File|null
     * @Assert\Image(maxSize="2M", maxSizeMessage="Image trop volumineuse (maximum 2Mb)")
     * @Assert\Image(mimeTypes = {"image/jpeg", "image/jpg", "image/png"}, mimeTypesMessage = "Mauvais format d'image (jpeg, jpg et png)")
    **/
    private $couvertureFile;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $couverture = null;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: EmploisTemps::class, cascade: ["persist"])]
    private Collection $emploisTemps;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ServiceSignale::class)]
    private Collection $serviceSignales;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: Remboursement::class)]
    private Collection $remboursements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuPrestation = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Categorie $categorie = null;

    #[ORM\Column(nullable: true)]
    private ?bool $homeStudio = null;

    #[ORM\Column(nullable: true)]
    private ?bool $endRegister = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pseudo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $usePseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagramId = null;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: Remboursement::class)]
    private Collection $vendeursremboursements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rib = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paypal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $recevedPaiement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeAccountId = null;

    #[ORM\OneToMany(mappedBy: 'vendeur', targetEntity: AvisReponse::class)]
    private Collection $avisReponses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Competence::class)]
    private Collection $competences;

    #[ORM\Column(nullable: true)]
    private ?int $numberAvis = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $denomination = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroTVA = null;

    public function fullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function nickName(): string
    {
        $prenom = ucfirst($this->prenom ?? '');
        $initialNom = strtoupper(substr($this->nom ?? '', 0, 1));

        return $prenom . '.' . $initialNom;
    }

    public function hasRole(string $role): string
    {
        return in_array($role, $this->roles, true);
    }

    public function showOnlyOnlineService(int $max = 3, int $serviceId): array
    {
        $res = [];
       foreach ($this->microservices as $microservice) {
           if (
               $microservice->getOnline()
               && !$microservice->getOffline()
               && $microservice->getId() !== $serviceId
           ) {
               $res[] = $microservice;

               if (count($res) >= $max) {
                   break;
               }
           }
       }

       return $res;
    }

    public function __construct()
    {
        $this->microservices = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->suivis = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->senderconversations = new ArrayCollection();
        $this->destinatairesCommandes = new ArrayCollection();
        $this->commandeMessages = new ArrayCollection();
        $this->retraits = new ArrayCollection();
        $this->realisations = new ArrayCollection();
        $this->emploisTemps = new ArrayCollection();
        $this->serviceSignales = new ArrayCollection();
        $this->remboursements = new ArrayCollection();
        $this->vendeursremboursements = new ArrayCollection();
        $this->avisReponses = new ArrayCollection();
        $this->competences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getApropos(): ?string
    {
        return $this->apropos;
    }

    public function setApropos(?string $apropos): self
    {
        $this->apropos = $apropos;

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

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setCouvertureFile(?File $couvertureFile = null): void
    {
        $this->couvertureFile = $couvertureFile;

        if (null !== $couvertureFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    public function getCouvertureFile(): ?File
    {
        return $this->couvertureFile;
    }

    public function serialize() {

        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
        ));

    }

    public function unserialize($serialized) {

        list (
            $this->id,
            $this->email,
            $this->password,
        ) = unserialize($serialized);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCompte(): ?string
    {
        return $this->compte;
    }

    public function setCompte(string $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getNameUrl(): ?string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(string $nameUrl): self
    {
        $this->nameUrl = $nameUrl;

        return $this;
    }

    /**
     * @return Collection|Microservice[]
     */
    public function getMicroservices(): Collection
    {
        return $this->microservices;
    }

    public function addMicroservice(Microservice $microservice): self
    {
        if (!$this->microservices->contains($microservice)) {
            $this->microservices[] = $microservice;
            $microservice->setVendeur($this);
        }

        return $this;
    }

    public function removeMicroservice(Microservice $microservice): self
    {
        if ($this->microservices->removeElement($microservice)) {
            // set the owning side to null (unless already changed)
            if ($microservice->getVendeur() === $this) {
                $microservice->setVendeur(null);
            }
        }

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPortefeuille(): ?Portefeuille
    {
        return $this->portefeuille;
    }

    public function setPortefeuille(?Portefeuille $portefeuille): self
    {
        // unset the owning side of the relation if necessary
        if ($portefeuille === null && $this->portefeuille !== null) {
            $this->portefeuille->setVendeur(null);
        }

        // set the owning side of the relation if necessary
        if ($portefeuille !== null && $portefeuille->getVendeur() !== $this) {
            $portefeuille->setVendeur($this);
        }

        $this->portefeuille = $portefeuille;

        return $this;
    }

    public function getAbonnement(): ?Abonnement
    {
        return $this->abonnement;
    }

    public function setAbonnement(?Abonnement $abonnement): self
    {
        // unset the owning side of the relation if necessary
        if ($abonnement === null && $this->abonnement !== null) {
            $this->abonnement->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($abonnement !== null && $abonnement->getUser() !== $this) {
            $abonnement->setUser($this);
        }

        $this->abonnement = $abonnement;

        return $this;
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
            $commande->setClient($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getClient() === $this) {
                $commande->setClient(null);
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
            $avi->setClient($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getClient() === $this) {
                $avi->setClient(null);
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
            $favori->setClient($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): self
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getClient() === $this) {
                $favori->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Suivis[]
     */
    public function getSuivis(): Collection
    {
        return $this->suivis;
    }

    public function addSuivi(Suivis $suivi): self
    {
        if (!$this->suivis->contains($suivi)) {
            $this->suivis[] = $suivi;
            $suivi->setVendeur($this);
        }

        return $this;
    }

    public function removeSuivi(Suivis $suivi): self
    {
        if ($this->suivis->removeElement($suivi)) {
            // set the owning side to null (unless already changed)
            if ($suivi->getVendeur() === $this) {
                $suivi->setVendeur(null);
            }
        }

        return $this;
    }

    /**
     * Permet de savoir si l'utilisateur suis déjà ce vendeur
     *
     * @param User $user
     * @return boolean
     */
    public function isFollowed(User $user){
        foreach($this->suivis as $suivi){
            if($suivi->getClient() === $user) return true;
        }

        return false;
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
            $conversation->setUser1($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getUser1() === $this) {
                $conversation->setUser1(null);
            }
        }

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
            $message->setAuteur($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuteur() === $this) {
                $message->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getSenderconversations(): Collection
    {
        return $this->senderconversations;
    }

    public function addSenderconversation(Conversation $senderconversation): self
    {
        if (!$this->senderconversations->contains($senderconversation)) {
            $this->senderconversations[] = $senderconversation;
            $senderconversation->setSender($this);
        }

        return $this;
    }

    public function removeSenderconversation(Conversation $senderconversation): self
    {
        if ($this->senderconversations->removeElement($senderconversation)) {
            // set the owning side to null (unless already changed)
            if ($senderconversation->getSender() === $this) {
                $senderconversation->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Commande[]
     */
    public function getDestinatairesCommandes(): Collection
    {
        return $this->destinatairesCommandes;
    }

    public function addDestinatairesCommandes(Commande $destinatairesCommandes): self
    {
        if (!$this->destinatairesCommandes->contains($destinatairesCommandes)) {
            $this->destinatairesCommandes[] = $destinatairesCommandes;
            $destinatairesCommandes->setDestinataire($this);
        }

        return $this;
    }

    public function removeDestinatairesCommandes(Commande $destinatairesCommandes): self
    {
        if ($this->destinatairesCommandes->removeElement($destinatairesCommandes)) {
            // set the owning side to null (unless already changed)
            if ($destinatairesCommandes->getDestinataire() === $this) {
                $destinatairesCommandes->setDestinataire(null);
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
            $commandeMessage->setUser($this);
        }

        return $this;
    }

    public function removeCommandeMessage(CommandeMessage $commandeMessage): self
    {
        if ($this->commandeMessages->removeElement($commandeMessage)) {
            // set the owning side to null (unless already changed)
            if ($commandeMessage->getUser() === $this) {
                $commandeMessage->setUser(null);
            }
        }

        return $this;
    }

    public function getReseauAvatar(): ?string
    {
        return $this->reseauAvatar;
    }

    public function setReseauAvatar(?string $reseauAvatar): self
    {
        $this->reseauAvatar = $reseauAvatar;

        return $this;
    }

    public function getFacebookId(): ?int
    {
        return $this->facebookId;
    }

    public function setFacebookId(?int $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * @return Collection<int, Retrait>
     */
    public function getRetraits(): Collection
    {
        return $this->retraits;
    }

    public function addRetrait(Retrait $retrait): self
    {
        if (!$this->retraits->contains($retrait)) {
            $this->retraits->add($retrait);
            $retrait->setVendeur($this);
        }

        return $this;
    }

    public function removeRetrait(Retrait $retrait): self
    {
        if ($this->retraits->removeElement($retrait)) {
            // set the owning side to null (unless already changed)
            if ($retrait->getVendeur() === $this) {
                $retrait->setVendeur(null);
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
            $realisation->setVendeur($this);
        }

        return $this;
    }

    public function removeRealisation(Realisation $realisation): self
    {
        if ($this->realisations->removeElement($realisation)) {
            // set the owning side to null (unless already changed)
            if ($realisation->getVendeur() === $this) {
                $realisation->setVendeur(null);
            }
        }

        return $this;
    }

    public function getCouverture(): ?string
    {
        return $this->couverture;
    }

    public function setCouverture(?string $couverture): self
    {
        $this->couverture = $couverture;

        return $this;
    }

    /**
     * @return Collection<int, EmploisTemps>
     */
    public function getEmploisTemps(): Collection
    {
        return $this->emploisTemps;
    }

    public function addEmploisTemp(EmploisTemps $emploisTemp): self
    {
        if (!$this->emploisTemps->contains($emploisTemp)) {
            $this->emploisTemps->add($emploisTemp);
            $emploisTemp->setVendeur($this);
        }

        return $this;
    }

    public function removeEmploisTemp(EmploisTemps $emploisTemp): self
    {
        if ($this->emploisTemps->removeElement($emploisTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploisTemp->getVendeur() === $this) {
                $emploisTemp->setVendeur(null);
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
            $serviceSignale->setUser($this);
        }

        return $this;
    }

    public function removeServiceSignale(ServiceSignale $serviceSignale): self
    {
        if ($this->serviceSignales->removeElement($serviceSignale)) {
            // set the owning side to null (unless already changed)
            if ($serviceSignale->getUser() === $this) {
                $serviceSignale->setUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        if ($this->getEmail()) {
            return $this->getEmail();
        }
        return $this->getId();
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
            $remboursement->setUser($this);
        }

        return $this;
    }

    public function removeRemboursement(Remboursement $remboursement): self
    {
        if ($this->remboursements->removeElement($remboursement)) {
            // set the owning side to null (unless already changed)
            if ($remboursement->getUser() === $this) {
                $remboursement->setUser(null);
            }
        }

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

    public function getLieuPrestation(): ?string
    {
        return $this->lieuPrestation;
    }

    public function setLieuPrestation(?string $lieuPrestation): self
    {
        $this->lieuPrestation = $lieuPrestation;

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

    public function isHomeStudio(): ?bool
    {
        return $this->homeStudio;
    }

    public function setHomeStudio(?bool $homeStudio): self
    {
        $this->homeStudio = $homeStudio;

        return $this;
    }

    public function isEndRegister(): ?bool
    {
        return $this->endRegister;
    }

    public function setEndRegister(?bool $endRegister): self
    {
        $this->endRegister = $endRegister;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isUsePseudo(): ?bool
    {
        return $this->usePseudo;
    }

    public function setUsePseudo(?bool $usePseudo): self
    {
        $this->usePseudo = $usePseudo;

        return $this;
    }

    public function getInstagramId(): ?string
    {
        return $this->instagramId;
    }

    public function setInstagramId(?string $instagramId): self
    {
        $this->instagramId = $instagramId;

        return $this;
    }

    /**
     * @return Collection<int, Remboursement>
     */
    public function getVendeursremboursements(): Collection
    {
        return $this->vendeursremboursements;
    }

    public function addVendeursremboursement(Remboursement $vendeursremboursement): self
    {
        if (!$this->vendeursremboursements->contains($vendeursremboursement)) {
            $this->vendeursremboursements->add($vendeursremboursement);
            $vendeursremboursement->setVendeur($this);
        }

        return $this;
    }

    public function removeVendeursremboursement(Remboursement $vendeursremboursement): self
    {
        if ($this->vendeursremboursements->removeElement($vendeursremboursement)) {
            // set the owning side to null (unless already changed)
            if ($vendeursremboursement->getVendeur() === $this) {
                $vendeursremboursement->setVendeur(null);
            }
        }

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getRib(): ?string
    {
        return $this->rib;
    }

    public function setRib(?string $rib): self
    {
        $this->rib = $rib;

        return $this;
    }

    public function getPaypal(): ?string
    {
        return $this->paypal;
    }

    public function setPaypal(?string $paypal): self
    {
        $this->paypal = $paypal;

        return $this;
    }

    public function getStripe(): ?string
    {
        return $this->stripe;
    }

    public function setStripe(?string $stripe): self
    {
        $this->stripe = $stripe;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function isRecevedPaiement(): ?bool
    {
        return $this->recevedPaiement;
    }

    public function setRecevedPaiement(?bool $recevedPaiement): self
    {
        $this->recevedPaiement = $recevedPaiement;

        return $this;
    }

    public function getStripeAccountId(): ?string
    {
        return $this->stripeAccountId;
    }

    public function setStripeAccountId(?string $stripeAccountId): self
    {
        $this->stripeAccountId = $stripeAccountId;

        return $this;
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
            $avisReponse->setVendeur($this);
        }

        return $this;
    }

    public function removeAvisReponse(AvisReponse $avisReponse): self
    {
        if ($this->avisReponses->removeElement($avisReponse)) {
            // set the owning side to null (unless already changed)
            if ($avisReponse->getVendeur() === $this) {
                $avisReponse->setVendeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Competence>
     */
    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function addCompetence(Competence $competence): self
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
            $competence->setUser($this);
        }

        return $this;
    }

    public function removeCompetence(Competence $competence): self
    {
        if ($this->competences->removeElement($competence)) {
            // set the owning side to null (unless already changed)
            if ($competence->getUser() === $this) {
                $competence->setUser(null);
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

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(?string $denomination): self
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getNumeroTVA(): ?string
    {
        return $this->numeroTVA;
    }

    public function setNumeroTVA(?string $numeroTVA): self
    {
        $this->numeroTVA = $numeroTVA;

        return $this;
    }

    public function getLinkedinId(): ?string
    {
        return $this->linkedinId;
    }

    public function setLinkedinId(?string $linkedinId): self
    {
        $this->linkedinId = $linkedinId;

        return $this;
    }
}
