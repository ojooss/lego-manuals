<?php

namespace App\Entity;

use App\Repository\SetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SetRepository::class)
 * @ORM\Table(
 *     name="`set`",
 *     indexes={
 *          @ORM\Index(name="set_number", columns={"number"}),
 *          @ORM\Index(name="set_name", columns={"name"})
 *     }
 * )
 */
class Set
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="integer", unique=true, nullable=true)
     */
    private ?int $number;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    private string $name;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Manual",
     *      mappedBy="set",
     *      cascade={"persist"},
     *      orphanRemoval=true
     * )
     */
    private Collection $manuals;

    /**
     * Set constructor.
     */
    public function __construct()
    {
        $this->manuals = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     * @return $this
     */
    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Add manual
     *
     * @param Manual $manual
     *
     * @return Set
     */
    public function addManual(Manual $manual): self
    {
        $manual->setSet($this);

        $this->manuals->add($manual);

        return $this;
    }

    /**
     * Remove manual
     *
     * @param Manual $manual
     * @return Set
     */
    public function removeManual(Manual $manual): self
    {
        $this->manuals->removeElement($manual);

        return $this;
    }

    /**
     * Get manuals
     *
     * @return Collection
     */
    public function getManuals(): Collection
    {
        return $this->manuals;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getNumber().' '.$this->getName() . ' (' . count($this->manuals) . ' Dokumente)';
    }

}
