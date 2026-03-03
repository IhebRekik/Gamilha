<?php
// 📁 src/Entity/CoachingVideo.php

namespace App\Entity;

use App\Repository\CoachingVideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoachingVideoRepository::class)]
class CoachingVideo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit faire au moins {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Veuillez entrer une URL valide')]
    private ?string $url = null;

    private ?File $videoFile = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le niveau est obligatoire')]
    #[Assert\Choice(
        choices: ['debutant', 'intermediaire', 'avance'],
        message: 'Veuillez choisir un niveau valide'
    )]
    private ?string $niveau = null;

    #[ORM\Column]
    private bool $premium ;

    /**
     * Durée en secondes — utilisée pour les statistiques de progression.
     * Ex: 600 = 10 minutes
     */
    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Veuillez sélectionner une playlist')]
    private ?Playlist $playlist = null;

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): self { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getUrl(): ?string { return $this->url; }
    public function setUrl(?string $url): self { $this->url = $url; return $this; }

    public function getVideoFile(): ?File { return $this->videoFile; }
    public function setVideoFile(?File $videoFile): self { $this->videoFile = $videoFile; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(string $niveau): self { $this->niveau = $niveau; return $this; }

    public function isPremium(): ?bool { return $this->premium; }
    public function setPremium(bool $premium): self { $this->premium = $premium; return $this; }

    public function getDuration(): ?int { return $this->duration; }
    public function setDuration(?int $duration): self { $this->duration = $duration; return $this; }

    public function getPlaylist(): ?Playlist { return $this->playlist; }
    public function setPlaylist(?Playlist $playlist): self { $this->playlist = $playlist; return $this; }
}