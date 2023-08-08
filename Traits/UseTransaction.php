<?php

namespace App\Traits;

use Closure;
use Throwable;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

trait UseTransaction
{
    /**
     * Use transaction to perform given action, otherwise report and log
     */
    protected function useTransaction(Closure $callable): mixed
    {
        try {
            return DB::transaction(fn () => $callable(), 3);
        } catch (Throwable $ex) {
            report($ex);

            if (app()->environment(['local', 'staging'])) {
                dump(app()->environment());
                dd($ex);
            }

            Notification::make()
                ->warning()
                ->title(__('logs.fail_title'))
                ->body(__('logs.fail_message'))
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    /**
     * Convert all items to string.
     */
    public function stringify($data): array
    {
        return array_map(function ($item) {
            return is_array($item) ? json_encode($item, JSON_OBJECT_AS_ARRAY) : (string) $item;
        }, $data);
    }

    public static function useStaticTransaction(Closure $callable, $action): mixed
    {
        try {
            return DB::transaction(fn () => $callable(), 3);
        } catch (Throwable $ex) {
            report($ex);

            if (app()->environment(['local', 'staging'])) {
                dump(app()->environment());
                dd($ex);
            }

            Notification::make()
                ->warning()
                ->title(__('logs.fail_title'))
                ->body(__('logs.fail_message'))
                ->persistent()
                ->send();

            $action->halt();
            $action->cancel();
        }
    }
}
