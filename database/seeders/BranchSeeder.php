<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $companyName = config('app.name', 'Percetakan');

        Branch::firstOrCreate(
            ['is_main' => true],
            [
                'name' => $companyName . ' (Induk)',
                'address' => config('app.company_address', 'Alamat belum diatur.'),
                'phone' => config('app.company_phone', '-'),
                'email' => config('mail.from.address', '-'),
            ]
        );
    }
}
