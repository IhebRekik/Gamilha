<?php

namespace App\Entity;

use App\Repository\GameMatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GameMatchRepository::class)]
#[ORM\Table(name: '`match`')]
class GameMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idMatch')]
    private ?int $idMatch = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'dateMatch')]
    #[Assert\NotBlank(message: 'La date du match est obligatoire.')]
    private ?\DateTimeInterface $dateMatch = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le tour est obligatoire.')]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    private ?int $tour = null;

    #[ORM\Column(name: 'scoreEquipeA', options: ['default' => 0])]
    private int $scoreEquipeA = 0;

    #[ORM\Column(name: 'scoreEquipeB', options: ['default' => 0])]
    private int $scoreEquipeB = 0;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le statut du match est obligatoire.')]
    #[Assert\Choice(choices: ['à venir', 'en cours', 'terminé'], message: 'Statut invalide.')]
    private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'matchsEquipeA')]
    #[ORM\JoinColumn(name: 'equipeA_id', referencedColumnName: 'idEquipe', nullable: false)]
    #[Assert\NotBlank(message: 'L\'équipe A est obligatoire.')]
    private ?Equipe $equipeA = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'matchsEquipeB')]
    #[ORM\JoinColumn(name: 'equipeB_id', referencedColumnName: 'idEquipe', nullable: false)]
    #[Assert\NotBlank(message: 'L\'équipe B est obligatoire.')]
    private ?Equipe $equipeB = null;

    #[ORM\ManyToOne(targetEntity: Bracket::class, inversedBy: 'matchs')]
    #[ORM\JoinColumn(name: 'idBracket', referencedColumnName: 'idBracket', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank(message: 'Le bracket est obligatoire.')]
    private ?Bracket $bracket = null;

    public function getIdMatch(): ?int
    {
        return $this->idMatch;
    }

    public function getDateMatch(): ?\DateTimeInterface
    {
        return $this->dateMatch;
    }

    public function setDateMatch(\DateTimeInterface $dateMatch): static
    {
        $this->dateMatch = $dateMatch;
        return $this;
    }

    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(int $tour): static
    {
        $this->tour = $tour;
        return $this;
    }

    public function getScoreEquipeA(): int
    {
        return $this->scoreEquipeA;
    }

    public function setScoreEquipeA(int $scoreEquipeA): static
    {
        $this->scoreEquipeA = $scoreEquipeA;
        return $this;
    }

    public function getScoreEquipeB(): int
    {
        return $this->scoreEquipeB;
    }

    public function setScoreEquipeB(int $scoreEquipeB): static
    {
        $this->scoreEquipeB = $scoreEquipeB;
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

    public function getEquipeA(): ?Equipe
    {
        return $this->equipeA;
    }

    public function setEquipeA(?Equipe $equipeA): static
    {
        $this->equipeA = $equipeA;
        return $this;
    }

    public function getEquipeB(): ?Equipe
    {
        return $this->equipeB;
    }

    public function setEquipeB(?Equipe $equipeB): static
    {
        $this->equipeB = $equipeB;
        return $this;
    }

    public function getBracket(): ?Bracket
    {
        return $this->bracket;
    }

    public function setBracket(?Bracket $bracket): static
    {
        $this->bracket = $bracket;
        return $this;
    }

    #[Assert\IsTrue(message: 'Les deux équipes doivent être différentes.')]
    public function isEquipesDifferent(): bool
    {
        return $this->equipeA === null || $this->equipeB === null || $this->equipeA->getIdEquipe() !== $this->equipeB->getIdEquipe();
    }
}
