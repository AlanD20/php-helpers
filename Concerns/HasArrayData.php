<?php

namespace App\Concerns;

use Closure;

trait HasArrayData
{
    /**
     * Executes given closure when given data array has more than one elements
     * Return the result of closure
     */
    public function whenArrayHasElements(array $data, Closure $action): mixed
    {
        $result = null;

        if (count($data) > 0) {
            $result = $action->call($this, $data);
        }

        return $result;
    }
}
