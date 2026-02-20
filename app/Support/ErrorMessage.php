<?php

namespace App\Support;

use Illuminate\Support\Str;
use Throwable;

class ErrorMessage
{
    public static function for(Throwable $th, string $fallback, ?bool $showDetails = null, ?string $reference = null): string
    {
        $message = trim((string) $th->getMessage());
        $showDetails = $showDetails ?? false;

        if (!$showDetails) {
            if ($message === '') {
                return self::withReference($fallback, $reference);
            }

            $lower = Str::lower($message);

            if (str_contains($lower, 'duplicate entry')) {
                return self::withReference($fallback . ' Data sudah ada atau duplikat.', $reference);
            }

            if (str_contains($lower, 'integrity constraint violation') && str_contains($lower, 'foreign key')) {
                return self::withReference($fallback . ' Data terkait tidak ditemukan atau masih digunakan.', $reference);
            }

            if (
                str_contains($lower, 'cannot be null')
                || str_contains($lower, 'null value')
                || str_contains($lower, 'null constraint')
            ) {
                return self::withReference($fallback . ' Ada data wajib yang belum diisi.', $reference);
            }

            if (str_contains($lower, 'sqlstate')) {
                return self::withReference($fallback . ' Terjadi kesalahan pada database. Periksa data yang diinput.', $reference);
            }
            return self::withReference($fallback, $reference);
        }

        if ($message === '') {
            return self::withReference($fallback . ' Detail: Tidak ada pesan error.', $reference);
        }

        $message = self::sanitize($message);
        return self::withReference($fallback . ' Detail: ' . Str::limit($message, 220), $reference);
    }

    protected static function sanitize(string $message): string
    {
        return Str::of($message)
            ->replace("\n", ' ')
            ->replace("\r", ' ')
            ->squish()
            ->toString();
    }

    protected static function withReference(string $message, ?string $reference): string
    {
        if (!$reference) {
            return $message;
        }

        return $message . ' (Kode: ' . $reference . ')';
    }
}
