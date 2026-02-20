<?php

namespace App\Support;

use Illuminate\Support\Str;
use Throwable;

class ErrorMessage
{
    public static function for(Throwable $th, string $fallback): string
    {
        $message = trim((string) $th->getMessage());
        if ($message === '') {
            return $fallback;
        }

        $lower = Str::lower($message);

        if (str_contains($lower, 'duplicate entry')) {
            return $fallback . ' Data sudah ada atau duplikat.';
        }

        if (str_contains($lower, 'integrity constraint violation') && str_contains($lower, 'foreign key')) {
            return $fallback . ' Data terkait tidak ditemukan atau masih digunakan.';
        }

        if (
            str_contains($lower, 'cannot be null')
            || str_contains($lower, 'null value')
            || str_contains($lower, 'null constraint')
        ) {
            return $fallback . ' Ada data wajib yang belum diisi.';
        }

        if (str_contains($lower, 'sqlstate')) {
            return $fallback . ' Terjadi kesalahan pada database. Periksa data yang diinput.';
        }

        $message = Str::of($message)
            ->replace("\n", ' ')
            ->replace("\r", ' ')
            ->squish()
            ->toString();

        return $fallback . ' Detail: ' . Str::limit($message, 160);
    }
}
