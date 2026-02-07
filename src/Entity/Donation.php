<?php

namespace App\Entity;

use App\Repository\DonationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonationRepository::class)]

   // getters et setters


class Donation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $icon;

    #[ORM\Column]
    private float $amount;

    #[ORM\Column]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Stream::class, inversedBy: 'donations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stream $stream = null;
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // getters / setters …
}
