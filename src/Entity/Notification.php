<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
     #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedNotifications')]
#[ORM\JoinColumn(nullable: false)]
private ?User $receiver = null;

#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sentNotifications')]
#[ORM\JoinColumn(nullable: false)]
private ?User $sender = null;
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'notifications')]
    private ?Post $post = null;

    #[ORM\Column(type:"string", length:20)]
    private ?string $type = null; // 'LIKE' ou 'COMMENT'

    #[ORM\Column(type:"boolean")]
    private bool $isRead = false;

    #[ORM\Column(type:"datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    // ===== Getters & Setters =====
    public function getId(): ?int { return $this->id; }
    public function getSender(): ?User { return $this->sender; }
    public function setSender(User $sender): self { $this->sender = $sender; return $this; }
    public function getReceiver(): ?User { return $this->receiver; }
    public function setReceiver(User $receiver): self { $this->receiver = $receiver; return $this; }
    public function getPost(): ?Post { return $this->post; }
    public function setPost(?Post $post): self { $this->post = $post; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getIsRead(): bool { return $this->isRead; }
    public function setIsRead(bool $isRead): self { $this->isRead = $isRead; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
}