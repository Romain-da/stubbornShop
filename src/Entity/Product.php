<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le nom doit contenir au moins {{ limit }} caractères.")]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(min: 5, max: 1000, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private ?string $description = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "Le prix doit être un nombre valide.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $isFeatured = false;

    // Champs de stock pour différentes tailles
    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Le stock doit être un nombre positif.")]
    private ?int $stockXS = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Le stock doit être un nombre positif.")]
    private ?int $stockS = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Le stock doit être un nombre positif.")]
    private ?int $stockM = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Le stock doit être un nombre positif.")]
    private ?int $stockL = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Le stock doit être un nombre positif.")]
    private ?int $stockXL = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ========================
    // ====== GETTERS & SETTERS ======
    // ========================

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    // ========================
    // ====== STOCKS GETTERS & SETTERS ======
    // ========================

    public function getStockXS(): ?int
    {
        return $this->stockXS;
    }

    public function setStockXS(?int $stockXS): self
    {
        $this->stockXS = $stockXS;
        return $this;
    }

    public function getStockS(): ?int
    {
        return $this->stockS;
    }

    public function setStockS(?int $stockS): self
    {
        $this->stockS = $stockS;
        return $this;
    }

    public function getStockM(): ?int
    {
        return $this->stockM;
    }

    public function setStockM(?int $stockM): self
    {
        $this->stockM = $stockM;
        return $this;
    }

    public function getStockL(): ?int
    {
        return $this->stockL;
    }

    public function setStockL(?int $stockL): self
    {
        $this->stockL = $stockL;
        return $this;
    }

    public function getStockXL(): ?int
    {
        return $this->stockXL;
    }

    public function setStockXL(?int $stockXL): self
    {
        $this->stockXL = $stockXL;
        return $this;
    }
}
