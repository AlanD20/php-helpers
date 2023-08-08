<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasModel
{
    private ?Model $model = null;

    /**
     * Set model instance if required
     */
    public function setModel(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model instance
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }
}
