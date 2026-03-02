<?php

namespace App\Entity;

use App\Repository\StreamRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Donation;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StreamRepository::class)]
class Stream
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\OneToMany(mappedBy: "stream", targetEntity: Donation::class, cascade: ["persist"])]
    private Collection $donations;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le jeu est obligatoire.")]
    private ?string $game = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $viewers = 0;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['live', 'offline', 'ended'])]
    private string $status = 'live';

    #[ORM\ManyToOne(inversedBy: 'streams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(message: "URL invalide.")]
    private ?string $url = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $streamKey = null;
    #[ORM\Column(type: "boolean")]
    private bool $isLive = false;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $apiVideoId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $rtmpServer = null;

    // Getters & Setters pour les nouveaux champs

    public function getApiVideoId(): ?string
    {
        return $this->apiVideoId;
    }

    public function setApiVideoId(?string $apiVideoId): self
    {
        $this->apiVideoId = $apiVideoId;
        return $this;
    }

    public function getRtmpServer(): ?string
    {
        return $this->rtmpServer;
    }

    public function setRtmpServer(?string $rtmpServer): self
    {
        $this->rtmpServer = $rtmpServer;
        return $this;
    }
    public function isLive(): bool
    {
        return $this->isLive;
    }

    public function setIsLive(bool $isLive): self
    {
        $this->isLive = $isLive;
        return $this;
    }


    public function __construct()
    {
        $this->donations = new ArrayCollection();
        $this->viewers = 0;
        $this->status = 'live';
        $this->createdAt = new \DateTime();
        $this->streamKey = bin2hex(random_bytes(8));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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

    public function getGame(): ?string
    {
        return $this->game;
    }

    public function setGame(string $game): self
    {
        $this->game = $game;
        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    public function getViewers(): int
    {
        return $this->viewers;
    }

    public function setViewers(int $viewers): self
    {
        $this->viewers = $viewers;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDonations(): Collection
    {
        return $this->donations;
    }

    public function addDonation(Donation $donation): self
    {
        if (!$this->donations->contains($donation)) {
            $this->donations->add($donation);
            $donation->setStream($this);
        }
        return $this;
    }

    public function removeDonation(Donation $donation): self
    {
        if ($this->donations->removeElement($donation)) {
            if ($donation->getStream() === $this) {
                $donation->setStream(new Stream()); // FIXED
            }
        }
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
    public function getStreamKey(): ?string
    {
        return $this->streamKey;
    }
    public function setStreamKey(string $streamKey): self
    {
        $this->streamKey = $streamKey;
        return $this;
    }
}
