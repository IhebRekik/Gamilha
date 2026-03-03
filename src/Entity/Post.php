<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire")]
#[Assert\Length(
    min: 12,
    minMessage: "Le contenu doit contenir au moins {{ limit }} caractères"
)]
    private string $content ;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt ;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediaurl = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'post',cascade: ['persist'],
 orphanRemoval: true,fetch: 'LAZY')]
    private Collection $commentaires;

    

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, User>  ← AJOUT pour les likes
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_likes')]
    private Collection $likedBy;
    #[ORM\OneToMany(mappedBy: "post", targetEntity: Notification::class)]
private Collection $notifications;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->commentaires = new ArrayCollection();
        $this->likedBy = new ArrayCollection(); // ← AJOUT
                $this->notifications = new ArrayCollection();

        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getMediaurl(): ?string
    {
        return $this->mediaurl;
    }

    public function setMediaurl(?string $mediaurl): static
    {
        $this->mediaurl = $mediaurl;
        return $this;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setPost($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getPost() === $this) {
                $commentaire->setPost(null);
            }
        }
        return $this;
    }
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    // ──────────────────────────────────────────────
    // Likes - tout ce qui concerne les likes
    // ──────────────────────────────────────────────

/** @return Collection<int, User> */
    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function addLikedBy(User $user): self
    {
        if (!$this->likedBy->contains($user)) {
            $this->likedBy->add($user);
        }
        return $this;
    }

    public function removeLikedBy(User $user): self
    {
        $this->likedBy->removeElement($user);
        return $this;
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likedBy->contains($user);
    }

    // ✅ compteur dynamique
    public function getLikesCount(): int
    {
        return $this->likedBy->count();
    }
    
}