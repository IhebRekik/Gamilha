<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use BcMath\Number;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type d'abonnement est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le type doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $type ;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $options = [];
    public const FEATURES = [
        'Coching' => 'coching',
        'Evenement' => 'evenement',
        'Streaming' => 'streaming',
    ];
/**
 * @var Collection<int, UserAbonnement>
 */
#[ORM\OneToMany(
    mappedBy: "abonnement", 
    targetEntity: UserAbonnement::class,
)]
private Collection $userAbonnements;
    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être supérieur à 0.")]
    private float $prix ;

    #[ORM\Column(nullable: true)]
    private ?array $avantages = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $duree;

    /**
     * @var Collection<int, HistoriquePaiement>
     */
    #[ORM\OneToMany(targetEntity: HistoriquePaiement::class, mappedBy: 'abonnement', orphanRemoval: true)]
    private Collection $historiquePaiements;



    public function __construct()
    {
        $this->userAbonnements = new ArrayCollection();
        $this->historiquePaiements = new ArrayCollection();
         $this->type = '';
    }
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->avantages ?? []);
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Collection<int, UserAbonnement>
     */
    public function getUserAbonnements(): Collection
    {
        return $this->userAbonnements;
    }

    public function addUserAbonnement(UserAbonnement $userAbonnement): static
    {
        if (!$this->userAbonnements->contains($userAbonnement)) {
            $this->userAbonnements->add($userAbonnement);
            $userAbonnement->setAbonnement($this);
        }

        return $this;
    }

    public function removeUserAbonnement(UserAbonnement $userAbonnement): static
    {
        if ($this->userAbonnements->removeElement($userAbonnement)) {
            if ($userAbonnement->getAbonnement() === $this) {
                $userAbonnement->setAbonnement(null);
            }
        }

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getAvantages(): ?array
    {
        return $this->avantages;
    }

    public function setAvantages(?array $avantages): static
    {
        $this->avantages = $avantages;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return Collection<int, HistoriquePaiement>
     */
    public function getHistoriquePaiements(): Collection
    {
        return $this->historiquePaiements;
    }

    public function addHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if (!$this->historiquePaiements->contains($historiquePaiement)) {
            $this->historiquePaiements->add($historiquePaiement);
            $historiquePaiement->setAbonnement($this);
        }

        return $this;
    }

    public function removeHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if ($this->historiquePaiements->removeElement($historiquePaiement)) {
            // set the owning side to null (unless already changed)
            if ($historiquePaiement->getAbonnement() === $this) {
                $historiquePaiement->setAbonnement(null);
            }
        }

        return $this;
    }
    
}
