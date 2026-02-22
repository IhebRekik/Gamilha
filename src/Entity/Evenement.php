<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Entity\Equipe;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEvenement')]
    private ?int $idEvenement = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le jeu est obligatoire.')]
    #[Assert\Length(max: 50)]
    private ?string $jeu = null;

    #[ORM\Column(length: 20, name: 'typeEvenement')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['online', 'offline'], message: 'Choisir online ou offline.')]
    private ?string $typeEvenement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, name: 'dateDebut')]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, name: 'dateFin')]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    #[Assert\GreaterThanOrEqual(propertyPath: 'dateDebut', message: 'La date de fin doit être après la date de début.')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['prévu', 'en cours', 'terminé'], message: 'Statut invalide.')]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $regles = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $image = null;

    /** @var Collection<int, Bracket> */
    #[ORM\OneToMany(targetEntity: Bracket::class, mappedBy: 'evenement', cascade: ['remove'])]
    private Collection $brackets;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /** @var Collection<int, Equipe> */
    #[ORM\ManyToMany(targetEntity: Equipe::class)]
    #[ORM\JoinTable(name: 'evenement_equipe')]
    #[ORM\JoinColumn(name: 'idEvenement', referencedColumnName: 'idEvenement', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'idEquipe', referencedColumnName: 'idEquipe', onDelete: 'CASCADE')]
    private Collection $equipesParticipantes;

        #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->brackets = new ArrayCollection();
        $this->equipesParticipantes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();}

    public function getIdEvenement(): ?int
    {
        return $this->idEvenement;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getJeu(): ?string
    {
        return $this->jeu;
    }

    public function setJeu(string $jeu): static
    {
        $this->jeu = $jeu;
        return $this;
    }

    public function getTypeEvenement(): ?string
    {
        return $this->typeEvenement;
    }

    public function setTypeEvenement(string $typeEvenement): static
    {
        $this->typeEvenement = $typeEvenement;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getRegles(): ?string
    {
        return $this->regles;
    }

    public function setRegles(?string $regles): static
    {
        $this->regles = $regles;
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

    /**
     * @return Collection<int, Bracket>
     */
    public function getBrackets(): Collection
    {
        return $this->brackets;
    }

    public function addBracket(Bracket $bracket): static
    {
        if (!$this->brackets->contains($bracket)) {
            $this->brackets->add($bracket);
            $bracket->setEvenement($this);
        }
        return $this;
    }

    public function removeBracket(Bracket $bracket): static
    {
        if ($this->brackets->removeElement($bracket)) {
            if ($bracket->getEvenement() === $this) {
                $bracket->setEvenement(null);
            }
        }
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return Collection<int, Equipe>
     */
    public function getEquipesParticipantes(): Collection
    {
        return $this->equipesParticipantes;
    }

    public function addEquipesParticipante(Equipe $equipe): static
    {
        if (!$this->equipesParticipantes->contains($equipe)) {
            $this->equipesParticipantes->add($equipe);
        }
        return $this;
    }

    public function removeEquipesParticipante(Equipe $equipe): static
    {
        $this->equipesParticipantes->removeElement($equipe);
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->nom;
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
}
