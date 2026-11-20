<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactRepository::class)]
#[ApiResource]
class Fact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $fact = null;

    #[ORM\Column(length: 36)]
    private ?string $dogApiId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFact(): ?string
    {
        return $this->fact;
    }

    public function setFact(string $fact): static
    {
        $this->fact = $fact;

        return $this;
    }

    public function getDogApiId(): ?string
    {
        return $this->dogApiId;
    }

    public function setDogApiId(string $dogApiId): static
    {
        $this->dogApiId = $dogApiId;

        return $this;
    }
}
