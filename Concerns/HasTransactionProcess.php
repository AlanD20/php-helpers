<?php

namespace App\Concerns;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasTransactionProcess
{
    protected ?Closure $whenSuccess = null;

    protected ?Closure $whenFail = null;

    /**
     * Execute extra process after transaction success..
     */
    public function whenSuccess(Closure $action): static
    {
        $this->whenSuccess = $action;

        return $this;
    }

    /**
     * Execute extra process after transaction fails.
     */
    public function whenFail(Closure $action): static
    {
        $this->whenFail = $action;

        return $this;
    }

    /**
     * Process multiple queries inside a transaction where the result of the transaction
     * will be passed to the first parameter of the whenSuccess closure
     */
    protected function withTransaction(Closure $action, int $attempts = 3): static
    {
        try {
            DB::transaction(function () use ($action) {
                $result = $action->call($this);

                if ($this->whenSuccess) {
                    $this->whenSuccess->call($this, $result);
                }
            }, attempts: $attempts);
        } catch (\Exception $ex) {
            Log::error('Error with: ' . \class_basename($this) . $ex->getMessage());

            if ($this->whenFail) {
                $this->whenFail->call($this, $ex);
            }

            if (app()->environment('local', 'staging')) {
                dd($ex);
            }
        }

        return $this;
    }
}
