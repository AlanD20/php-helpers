<?php

namespace App\Concerns;

trait HasMake
{
    /**
     * Return a new class instance.
     */
    public static function make(): static
    {
        return new self;
    }
}
