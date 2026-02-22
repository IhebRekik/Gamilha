<?php

namespace App\Entity;

use App\Repository\AnalysisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalysisRepository::class)]
class Analysis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $videoName = null;

    #[ORM\Column(length: 5000, nullable: true)]
    private ?string $result = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    public function setVideoName(string $videoName): static
    {
        $this->videoName = $videoName;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;

        return $this;
    }
}
