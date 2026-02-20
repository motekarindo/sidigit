<?php

namespace App\Traits;

use App\Support\ErrorMessage;
use Throwable;

trait WithErrorToast
{
    protected function toastError(Throwable $th, string $fallback): void
    {
        $this->dispatch('toast', message: ErrorMessage::for($th, $fallback), type: 'error');
    }
}
