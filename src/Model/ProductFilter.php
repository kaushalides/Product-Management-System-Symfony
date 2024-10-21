<?php

namespace App\Model;

class ProductFilter
{
    private ?string $search = null;
    private ?float $minPrice = null;
    private ?float $maxPrice = null;
    private ?int $minStock = null;
    private ?int $maxStock = null;
    private ?string $dateFrom = null;
    private ?string $dateTo = null;

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    public function setMinPrice(?float $minPrice): self
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(?float $maxPrice): self
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    public function getMinStock(): ?int
    {
        return $this->minStock;
    }

    public function setMinStock(?int $minStock): self
    {
        $this->minStock = $minStock;
        return $this;
    }

    public function getMaxStock(): ?int
    {
        return $this->maxStock;
    }

    public function setMaxStock(?int $maxStock): self
    {
        $this->maxStock = $maxStock;
        return $this;
    }

    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?string $dateFrom): self
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }

    public function setDateTo(?string $dateTo): self
    {
        $this->dateTo = $dateTo;
        return $this;
    }
}
