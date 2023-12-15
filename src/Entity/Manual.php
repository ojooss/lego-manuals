<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection SpellCheckingInspection */

namespace App\Entity;

use App\Repository\ManualRepository;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ManualRepository::class)]
class Manual implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'manual.filename.not_blank')]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'manual.covername.not_blank')]
    private string $covername;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'manual.url.not_blank')]
    #[Assert\Url(message: 'manual.url.url')]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: 'Set', inversedBy: 'manuals')]
    #[ORM\JoinColumn(nullable: false)]
    private Set $set;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return void
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Set
     */
    public function getSet(): Set
    {
        return $this->set;
    }

    /**
     * @param Set $set
     * @return Manual
     */
    public function setSet(Set $set): self
    {
        $this->set = $set;

        return $this;
    }

    /**
     * @return string
     */
    public function getCovername(): string
    {
        return $this->covername;
    }

    /**
     * @param string $covername
     * @return Manual
     */
    public function setCovername(string $covername): self
    {
        $this->covername = $covername;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getUrl();
    }

}
