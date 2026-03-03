<?php

namespace App\Entity;

use App\Repository\DonationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Stream;

#[ORM\Entity(repositoryClass: DonationRepository::class)]
class Donation
{
    #[ORM\Id, ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;
    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: "string", length: 255)]
    private string $donorName;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;
    #[ORM\ManyToOne(inversedBy: 'donations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'donations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Stream $stream = null;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }
    public function getDonorName(): string
    {
        return $this->donorName;
    }
    public function setDonorName(string $donorName): self
    {
        $this->donorName = $donorName;
        return $this;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
    public function getStream(): Stream
    {
        return $this->stream;
    }
    public function setStream(Stream $stream): self
    {
        $this->stream = $stream;
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
}
