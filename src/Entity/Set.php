<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\SetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table(name: '`set`')]
#[ORM\Index(columns: ['number'], name: 'set_number')]
#[ORM\Index(columns: ['name'], name: 'set_name')]
#[ORM\Entity(repositoryClass: SetRepository::class)]
class Set implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER, unique: true, nullable: true)]
    #[Assert\NotBlank(message: 'set.number.not_blank')]
    #[Assert\Positive(message: 'set.number.number')]
    private ?int $number = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    #[Assert\NotBlank(message: 'set.name.not_blank')]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'set', targetEntity: 'Manual', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $manuals;

    /**
     * Set constructor.
     */
    public function __construct()
    {
        $this->manuals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Add manual
     *
     *
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
     * @noinspection PhpUnused
     */
    public function removeManual(Manual $manual): self
    {
        $this->manuals->removeElement($manual);

        return $this;
    }

    /**
     * Get manuals
     *
     * @return Collection<Manual>
     */
    public function getManuals(): Collection
    {
        return $this->manuals;
    }

    public function __toString(): string
    {
        return $this->getNumber().' '.$this->getName() . ' (' . count($this->manuals) . ' Dokumente)';
    }
}
