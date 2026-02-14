<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
#[ORM\Table(name: 'equipe')]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEquipe')]
    private ?int $idEquipe = null;

    #[ORM\Column(length: 100, name: 'nomEquipe')]
    #[Assert\NotBlank(message: 'Le nom de l\'équipe est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $nomEquipe = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(max: 10)]
    private ?string $tag = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $logo = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $pays = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, name: 'dateCreation')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['amateur', 'semi-pro', 'pro'], message: 'Niveau invalide.')]
    private ?string $niveau = null;

    /** @var Collection<int, GameMatch> */
    #[ORM\OneToMany(targetEntity: GameMatch::class, mappedBy: 'equipeA')]
    private Collection $matchsEquipeA;

    /** @var Collection<int, GameMatch> */
    #[ORM\OneToMany(targetEntity: GameMatch::class, mappedBy: 'equipeB')]
    private Collection $matchsEquipeB;

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'equipes')]
    #[ORM\JoinTable(name: 'equipe_user')]
    #[ORM\JoinColumn(name: 'equipe_id', referencedColumnName: 'idEquipe')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private Collection $members;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $owner = null;

    public function __construct()
    {
        $this->matchsEquipeA = new ArrayCollection();
        $this->matchsEquipeB = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public function getIdEquipe(): ?int
    {
        return $this->idEquipe;
    }

    public function getNomEquipe(): ?string
    {
        return $this->nomEquipe;
    }

    public function setNomEquipe(?string $nomEquipe): static
    {
        $this->nomEquipe = $nomEquipe;
        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): static
    {
        $this->tag = $tag;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(?string $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }

    /**
     * @return Collection<int, GameMatch>
     */
    public function getMatchsEquipeA(): Collection
    {
        return $this->matchsEquipeA;
    }

    /**
     * @return Collection<int, GameMatch>
     */
    public function getMatchsEquipeB(): Collection
    {
        return $this->matchsEquipeB;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }
        return $this;
    }

    public function removeMember(User $member): static
    {
        $this->members->removeElement($member);
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function __toString(): string
    {
        return (string) ($this->nomEquipe ?? $this->tag ?? 'Equipe #' . $this->idEquipe);
    }
}
