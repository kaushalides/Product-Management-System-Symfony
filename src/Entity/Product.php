<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'The product name cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'The product name cannot exceed {{ limit }} characters.')]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'The description cannot be blank.')]
    private $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'The price cannot be blank.')]
    #[Assert\Type(type: 'numeric', message: 'The price must be a valid number.')]
    #[Assert\GreaterThan(value: 0, message: 'The price must be greater than 0.')]
    private $price;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'The stock quantity cannot be blank.')]
    #[Assert\Type(type: 'integer', message: 'The stock quantity must be an integer.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'The stock quantity cannot be negative.')]
    private $stockQuantity;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private $createdDatetime;

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

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): self
    {
        $this->stockQuantity = $stockQuantity;
        return $this;
    }

    public function getCreatedDatetime(): ?\DateTimeInterface
    {
        return $this->createdDatetime;
    }

    public function setCreatedDatetime(\DateTimeInterface $createdDatetime): self
    {
        $this->createdDatetime = $createdDatetime;
        return $this;
    }

    public function setCreatedDatetimeNow(): self
    {
        $this->createdDatetime = new \DateTime('now', new \DateTimeZone('Asia/Singapore'));
        return $this;
    }
}
