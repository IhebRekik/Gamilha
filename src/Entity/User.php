<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Team;
use App\Entity\Friend;
use App\Entity\Post;
use App\Entity\Commentaire;
use App\Entity\UserAbonnement;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Stream;
use App\Entity\Donation;
use App\Entity\Equipe;
use App\Entity\Notification;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;
    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'adresse email est obligatoire.")]
    #[Assert\Email(message: "Veuillez saisir une adresse email valide.")]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(
        min: 8,
        minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/",
        message: "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre."
    )]
    private ?string $password = null;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class, orphanRemoval: true)]
    private Collection $posts;

   #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commentaire::class, orphanRemoval: true)]
private Collection $commentaires;
    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Notification::class)]
private Collection $receivedNotifications;

#[ORM\OneToMany(mappedBy: 'sender', targetEntity: Notification::class)]
private Collection $sentNotifications;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        max: 50,
        maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $name = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profileImage = null;



    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $banUntil = null;

    #[ORM\Column(type: 'integer')]
    private int $reports = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;



    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Stream::class)]
    private Collection $streams;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Donation::class)]
    private Collection $donations;
    /**
     * @var Collection<int, Team>
     */
    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'members')]
private Collection $teams;

    /**
     * @var Collection<int, Friend>
     */
    #[ORM\OneToMany(targetEntity: Friend::class, mappedBy: 'user')]
    private Collection $friends;

    /**
     * @var Collection<int, Equipe>
     */
    #[ORM\ManyToMany(targetEntity: Equipe::class, mappedBy: 'members')]
    private Collection $equipes;

    /**
     * @var Collection<int, Equipe>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Equipe::class)]
private Collection $equipesOwned;
    public function getStreams(): Collection
    {
        return $this->streams;
    }


    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): static
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    public function getBanUntil(): ?\DateTimeInterface
    {
        return $this->banUntil;
    }

    public function setBanUntil(?\DateTimeImmutable $banUntil): self
    {
        $this->banUntil = $banUntil;
        return $this;
    }

    public function getReports(): int
    {
        return $this->reports;
    }

    public function setReports(int $reports): static
    {
        $this->reports = $reports;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }




    /**
     * @var Collection<int, UserAbonnement>
     */
    #[ORM\OneToMany(targetEntity: UserAbonnement::class, mappedBy: 'user')]
    private Collection $userAbonnements;

    /**
     * @var Collection<int, ChatMessage>
     */
    #[ORM\OneToMany(targetEntity: ChatMessage::class, mappedBy: 'sender')]
    private Collection $messagesSent;

    /**
     * @var Collection<int, ChatMessage>
     */
    #[ORM\OneToMany(targetEntity: ChatMessage::class, mappedBy: 'recipient')]
    private Collection $messagesRecipient;

    /**
     * @var Collection<int, ChatAi>
     */
    #[ORM\OneToMany(targetEntity: ChatAi::class, mappedBy: 'user', orphanRemoval: false)]
    private Collection $chatAis;

    /**
     * @var Collection<int, HistoriquePaiement>
     */
    #[ORM\OneToMany(targetEntity: HistoriquePaiement::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $historiquePaiements;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->userAbonnements = new ArrayCollection();
        $this->messagesSent = new ArrayCollection();
        $this->messagesRecipient = new ArrayCollection();
        $this->donations = new ArrayCollection();
        $this->streams = new ArrayCollection();
        $this->chatAis = new ArrayCollection();
        $this->historiquePaiements = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();

        $this->equipes = new ArrayCollection();
        $this->equipesOwned = new ArrayCollection();
        $this->commentaires = new ArrayCollection();

     $this->posts = new ArrayCollection();
     $this->receivedNotifications = new ArrayCollection();
     $this->sentNotifications = new ArrayCollection();
             $this->friends = new ArrayCollection();


    }

    /* ===================== TEAMS ===================== */

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addMember($this);
        }
        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            $team->removeMember($this);
        }
        return $this;
    }

    /* ===================== ABONNEMENTS ===================== */

    public function getUserAbonnements(): Collection
    {
        return $this->userAbonnements;
    }

    /* ===================== FRIENDS ===================== */

    public function getFriends(): Collection
    {
        return $this->friends;
    }


    public function addFriend(Friend $friend): static
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
            $friend->setUser($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getMessagesSent(): Collection
    {
        return $this->messagesSent;
    }

    public function addMessagesSent(ChatMessage $messagesSent): static
    {
        if (!$this->messagesSent->contains($messagesSent)) {
            $this->messagesSent->add($messagesSent);
            $messagesSent->setSender($this);
        }

        return $this;
    }

    public function removeMessagesSent(ChatMessage $messagesSent): static
    {
        if ($this->messagesSent->removeElement($messagesSent)) {
            // set the owning side to null (unless already changed)
            if ($messagesSent->getSender() === $this) {
                $messagesSent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getMessagesRecipient(): Collection
    {
        return $this->messagesRecipient;
    }

    public function addMessagesRecipient(ChatMessage $messagesRecipient): static
    {
        if (!$this->messagesRecipient->contains($messagesRecipient)) {
            $this->messagesRecipient->add($messagesRecipient);
            $messagesRecipient->setRecipient($this);
        }

        return $this;
    }

    public function removeMessagesRecipient(ChatMessage $messagesRecipient): static
    {
        if ($this->messagesRecipient->removeElement($messagesRecipient)) {
            // set the owning side to null (unless already changed)
            if ($messagesRecipient->getRecipient() === $this) {
                $messagesRecipient->setRecipient(null);
            }
        }

        return $this;
    }
    public function getDonations(): Collection
    {
        return $this->donations;
    }

    /**
     * @return Collection<int, ChatAi>
     */
    public function getChatAis(): Collection
    {
        return $this->chatAis;
    }

    public function addChatAi(ChatAi $chatAi): static
    {
        if (!$this->chatAis->contains($chatAi)) {
            $this->chatAis->add($chatAi);
            $chatAi->setUser($this);
        }

        return $this;
    }

    public function removeChatAi(ChatAi $chatAi): static
    {
        if ($this->chatAis->removeElement($chatAi)) {
            // set the owning side to null (unless already changed)
            if ($chatAi->getUser() === $this) {
                $chatAi->setUser(null);
            }
        }
        return $this;
    }
    /* ===================== EQUIPES ===================== */

    /**
     * @return Collection<int, Equipe>
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): static
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes->add($equipe);
            $equipe->addMember($this);
        }
        return $this;
    }

    public function removeEquipe(Equipe $equipe): static
    {
        if ($this->equipes->removeElement($equipe)) {
            $equipe->removeMember($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, HistoriquePaiement>
     */
    public function getHistoriquePaiements(): Collection
    {
        return $this->historiquePaiements;
    }

    public function addHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if (!$this->historiquePaiements->contains($historiquePaiement)) {
            $this->historiquePaiements->add($historiquePaiement);
            $historiquePaiement->setUser($this);
        }

        return $this;
    }

    public function removeHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if ($this->historiquePaiements->removeElement($historiquePaiement)) {
            // set the owning side to null (unless already changed)
            if ($historiquePaiement->getUser() === $this) {
                $historiquePaiement->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    /**
     * @return Collection<int, Equipe>
     */
    public function getEquipesOwned(): Collection
    {
        return $this->equipesOwned;
    }

    public function addEquipeOwned(Equipe $equipe): static
    {
        if (!$this->equipesOwned->contains($equipe)) {
            $this->equipesOwned->add($equipe);
            $equipe->setOwner($this);
        }
        return $this;
    }

    public function removeEquipeOwned(Equipe $equipe): static
    {
        if ($this->equipesOwned->removeElement($equipe)) {
            if ($equipe->getOwner() === $this) {
                $equipe->setOwner(null);
            }
        }
        return $this;
    }
    public function getReceivedNotifications(): Collection
{
    return $this->receivedNotifications;
}
}
