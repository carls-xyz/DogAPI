<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BreedGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BreedGroupRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_breed_group_dog_api_id', columns: ['dog_api_id'])]
#[UniqueEntity('dogApiId')]
#[ApiResource(
    normalizationContext: ['groups' => ['breedgroup:read']],
    denormalizationContext: ['groups' => ['breedgroup:write']],
)]
class BreedGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['breedgroup:read', 'breed:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['breedgroup:read', 'breedgroup:write', 'breed:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 36)]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[Groups(['breedgroup:read', 'breedgroup:write', 'breed:read'])]
    private ?string $dogApiId = null;

    /**
     * @var Collection<int, Breed>
     */
    #[ORM\OneToMany(targetEntity: Breed::class, mappedBy: 'breedGroup')]
    #[Groups(['breedgroup:read'])]
    private Collection $breeds;

    public function __construct()
    {
        $this->breeds = new ArrayCollection();
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

    public function getDogApiId(): ?string
    {
        return $this->dogApiId;
    }

    public function setDogApiId(string $dogApiId): static
    {
        $this->dogApiId = $dogApiId;

        return $this;
    }

    /**
     * @return Collection<int, Breed>
     */
    public function getBreeds(): Collection
    {
        return $this->breeds;
    }

    public function addBreed(Breed $breed): static
    {
        if (!$this->breeds->contains($breed)) {
            $this->breeds->add($breed);
            $breed->setBreedGroup($this);
        }

        return $this;
    }

    public function removeBreed(Breed $breed): static
    {
        if ($this->breeds->removeElement($breed)) {
            // set the owning side to null (unless already changed)
            if ($breed->getBreedGroup() === $this) {
                $breed->setBreedGroup(null);
            }
        }

        return $this;
    }
}
