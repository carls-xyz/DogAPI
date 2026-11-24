<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TemperamentTraitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TemperamentTraitRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_temperament_trait_name', columns: ['name'])]
#[UniqueEntity('name')]
#[ApiResource(
    normalizationContext: ['groups' => ['trait:read']],
    denormalizationContext: ['groups' => ['trait:write']],
)]
class TemperamentTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['trait:read', 'breed:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['trait:read', 'trait:write', 'breed:read'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Breed>
     */
    #[ORM\ManyToMany(targetEntity: Breed::class, mappedBy: 'traits')]
    #[Groups(['trait:read'])]
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
            $breed->addTrait($this);
        }

        return $this;
    }

    public function removeBreed(Breed $breed): static
    {
        if ($this->breeds->removeElement($breed)) {
            $breed->removeTrait($this);
        }

        return $this;
    }
}
