<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    /**
     * @var Collection<int, Besoin>
     */
    #[ORM\OneToMany(targetEntity: Besoin::class, mappedBy: 'category')]
    private Collection $besoins;

    public function __construct()
    {
        $this->besoins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Besoin>
     */
    public function getBesoins(): Collection
    {
        return $this->besoins;
    }

    public function addBesoin(Besoin $besoin): static
    {
        if (!$this->besoins->contains($besoin)) {
            $this->besoins->add($besoin);
            $besoin->setCategory($this);
        }

        return $this;
    }

    public function removeBesoin(Besoin $besoin): static
    {
        if ($this->besoins->removeElement($besoin)) {
            // set the owning side to null (unless already changed)
            if ($besoin->getCategory() === $this) {
                $besoin->setCategory(null);
            }
        }

        return $this;
    }
}
