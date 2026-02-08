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

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /* ===================== RELATIONS ===================== */

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'members')]
    private Collection $teams;

    #[ORM\OneToMany(targetEntity: UserAbonnement::class, mappedBy: 'user')]
    private Collection $userAbonnements;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class, orphanRemoval: true)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Friend::class, orphanRemoval: true)]
    private Collection $friends;

    /* ===================== CONSTRUCTOR ===================== */

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->userAbonnements = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->friends = new ArrayCollection();
    }

    /* ===================== GETTERS / SETTERS ===================== */

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

    /* ===================== POSTS ===================== */

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /* ===================== COMMENTAIRES ===================== */

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
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

   public function removeFriend(Friend $friend): static
{
    $this->friends->removeElement($friend);
    return $this;
}

}
