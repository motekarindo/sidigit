<?php

namespace App\Traits;

use App\Support\ErrorReporter;
use Illuminate\Validation\ValidationException;
use Throwable;

trait WithErrorToast
{
    protected function toastError(Throwable $th, string $fallback): void
    {
        $payload = ErrorReporter::report($th, $fallback);
        $this->dispatch('toast', message: $payload['message'], type: 'error', ref: $payload['ref']);
    }

    protected function toastValidation(ValidationException $e, ?string $fallback = null): void
    {
        $message = $e->validator->errors()->first();
        if (!$message) {
            $message = $fallback ?: 'Periksa kembali input. Ada data yang belum sesuai.';
        }

        $this->dispatch('toast', message: $message, type: 'warning');
    }
}
