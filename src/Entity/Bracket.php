<?php

namespace App\Entity;

use App\Repository\BracketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BracketRepository::class)]
#[ORM\Table(name: 'bracket')]
class Bracket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_Bracket')]
    private ?int $idBracket = null;

    #[ORM\Column(length: 50, name: 'typeBracket')]
    #[Assert\NotBlank(message: 'Le type de bracket est obligatoire.')]
    #[Assert\Choice(choices: ['single elimination', 'double elimination'], message: 'Choisir single elimination ou double elimination.')]
    private string $typeBracket ;

    #[ORM\Column(name: 'nombreTours')]
    #[Assert\NotBlank(message: 'Le nombre de tours est obligatoire.')]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero(message: 'Le nombre de tours doit être positif ou zéro.')]
    private int $nombreTours ;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(choices: ['en attente', 'en cours', 'terminé'], message: 'Statut invalide.')]
    private string $statut ;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: "brackets")]
#[ORM\JoinColumn(name: "evenement_id", referencedColumnName: "id", nullable: false)]
private ?Evenement $evenement = null;

    /** @var Collection<int, GameMatch> */
    #[ORM\OneToMany(targetEntity: GameMatch::class, mappedBy: 'bracket', orphanRemoval: true)]
    private Collection $matchs;

    public function __construct()
    {
        $this->matchs = new ArrayCollection();
    }

    public function getIdBracket(): ?int
    {
        return $this->idBracket;
    }

    public function getTypeBracket(): ?string
    {
        return $this->typeBracket;
    }

    public function setTypeBracket(string $typeBracket): static
    {
        $this->typeBracket = $typeBracket;
        return $this;
    }

    public function getNombreTours(): ?int
    {
        return $this->nombreTours;
    }

    public function setNombreTours(int $nombreTours): static
    {
        $this->nombreTours = $nombreTours;
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

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): static
    {
        $this->evenement = $evenement;
        return $this;
    }

    /**
     * @return Collection<int, GameMatch>
     */
    public function getMatchs(): Collection
    {
        return $this->matchs;
    }

    public function addMatch(GameMatch $match): static
    {
        if (!$this->matchs->contains($match)) {
            $this->matchs->add($match);
            $match->setBracket($this);
        }
        return $this;
    }

    public function removeMatch(GameMatch $match): static
    {
        if ($this->matchs->removeElement($match)) {
            if ($match->getBracket() === $this) {
                $match->setBracket(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s (ID: %d)', $this->typeBracket ?? '', $this->idBracket ?? 0);
    }
}
