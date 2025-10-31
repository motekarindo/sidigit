<?php

namespace App\Enums;

enum CustomerMemberType: string
{
    case UMUM = 'umum';
    case RESELLER = 'reseller';
    case SEKOLAH = 'sekolah';
    case PEMERINTAH = 'pemerintah';
    case SWASTA = 'swasta';

    public static function options(): array
    {
        return array_map(
            static fn (self $case) => $case->value,
            self::cases()
        );
    }
}
