<?php

namespace App\Support;

class UnitFormatter
{
    public static function label(?string $name): string
    {
        $raw = trim((string) $name);
        $normalized = strtolower($raw);

        if ($normalized === '') {
            return '';
        }

        return match ($normalized) {
            'm2', 'm^2', 'm²' => 'm2',
            'cm2', 'cm^2', 'cm²' => 'cm2',
            default => $raw,
        };
    }

    public static function quantity(float $value, ?string $unitName = null, int $decimals = 2): string
    {
        $formatted = number_format($value, $decimals, ',', '.');
        $unitLabel = self::label($unitName);

        return $unitLabel !== '' ? $formatted . ' ' . $unitLabel : $formatted;
    }
}
