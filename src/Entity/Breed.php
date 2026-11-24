<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BreedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BreedRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_breed_dog_api_id', columns: ['dog_api_id'])]
#[UniqueEntity('dogApiId')]
#[ApiResource(
    normalizationContext: ['groups' => ['breed:read']],
    denormalizationContext: ['groups' => ['breed:write']],
)]
class Breed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['breed:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['breed:read', 'breed:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(min: 1)]
    #[Groups(['breed:read', 'breed:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Groups(['breed:read', 'breed:write'])]
    private ?int $lifeMin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Assert\GreaterThanOrEqual(propertyPath: 'lifeMin')]
    #[Groups(['breed:read', 'breed:write'])]
    private ?int $lifeMax = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 5)]
    #[Groups(['breed:read', 'breed:write'])]
    private ?int $energy = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 5)]
    #[Groups(['breed:read', 'breed:write'])]
    private ?int $barking = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 5)]
    #[Groups(['breed:read', 'breed:write'])]
    private ?int $trainability = null;

    #[ORM\Column(length: 36)]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[Groups(['breed:read', 'breed:write'])]
    private ?string $dogApiId = null;

    #[ORM\ManyToOne(inversedBy: 'breeds')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['breed:read', 'breed:write'])]
    private ?BreedGroup $breedGroup = null;

    /**
     * @var Collection<int, TemperamentTrait>
     */
    #[ORM\ManyToMany(targetEntity: TemperamentTrait::class, inversedBy: 'breeds')]
    #[Groups(['breed:read', 'breed:write'])]
    private Collection $traits;

    public function __construct()
    {
        $this->traits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getLifeMin(): ?int
    {
        return $this->lifeMin;
    }

    public function setLifeMin(?int $lifeMin): static
    {
        $this->lifeMin = $lifeMin;

        return $this;
    }

    public function getLifeMax(): ?int
    {
        return $this->lifeMax;
    }

    public function setLifeMax(?int $lifeMax): static
    {
        $this->lifeMax = $lifeMax;

        return $this;
    }

    public function getEnergy(): ?int
    {
        return $this->energy;
    }

    public function setEnergy(?int $energy): static
    {
        $this->energy = $energy;

        return $this;
    }

    public function getBarking(): ?int
    {
        return $this->barking;
    }

    public function setBarking(?int $barking): static
    {
        $this->barking = $barking;

        return $this;
    }

    public function getTrainability(): ?int
    {
        return $this->trainability;
    }

    public function setTrainability(?int $trainability): static
    {
        $this->trainability = $trainability;

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

    public function getBreedGroup(): ?BreedGroup
    {
        return $this->breedGroup;
    }

    public function setBreedGroup(?BreedGroup $breedGroup): static
    {
        $this->breedGroup = $breedGroup;

        return $this;
    }

    /**
     * @return Collection<int, TemperamentTrait>
     */
    public function getTraits(): Collection
    {
        return $this->traits;
    }

    public function addTrait(TemperamentTrait $trait): static
    {
        if (!$this->traits->contains($trait)) {
            $this->traits->add($trait);
        }

        return $this;
    }

    public function removeTrait(TemperamentTrait $trait): static
    {
        $this->traits->removeElement($trait);

        return $this;
    }
}
