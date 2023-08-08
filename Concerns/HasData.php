<?php

namespace App\Concerns;

trait HasData
{
    private array $data = [];

    /**
     * Set data attribute if required
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data attribute
     */
    public function getData(): array
    {
        return $this->data;
    }
}
