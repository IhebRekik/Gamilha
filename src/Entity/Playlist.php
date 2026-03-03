<?php
// 📁 src/Entity/Playlist.php

namespace App\Entity;

use App\Repository\PlaylistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlaylistRepository::class)]
class Playlist
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
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit faire au moins {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le niveau est obligatoire')]
    #[Assert\Choice(
        choices: ['debutant', 'intermediaire', 'avance'],
        message: 'Veuillez choisir un niveau valide'
    )]
    private ?string $niveau = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La catégorie est obligatoire')]
    #[Assert\Choice(
        choices: ['action', 'aventure', 'sport', 'course'],
        message: 'Veuillez choisir une catégorie valide'
    )]
    private ?string $categorie = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'image est obligatoire")]
    private ?string $image = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt ;

    #[ORM\OneToMany(mappedBy: 'playlist', targetEntity: CoachingVideo::class, orphanRemoval: true)]
    private Collection $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(string $niveau): self { $this->niveau = $niveau; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(string $categorie): self { $this->categorie = $categorie; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    /** @return Collection<int, CoachingVideo> */
    public function getVideos(): Collection { return $this->videos; }

    public function addVideo(CoachingVideo $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setPlaylist($this);
        }
        return $this;
    }

    public function removeVideo(CoachingVideo $video): self
    {
        if ($this->videos->removeElement($video)) {
            if ($video->getPlaylist() === $this) {
                $video->setPlaylist(null);
            }
        }
        return $this;
    }
}
