<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FactRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_fact_dog_api_id', columns: ['dog_api_id'])]
#[UniqueEntity('dogApiId')]
#[ApiResource(
    normalizationContext: ['groups' => ['fact:read']],
    denormalizationContext: ['groups' => ['fact:write']],
)]
class Fact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fact:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['fact:read', 'fact:write'])]
    private ?string $fact = null;

    #[ORM\Column(length: 36)]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[Groups(['fact:read', 'fact:write'])]
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
