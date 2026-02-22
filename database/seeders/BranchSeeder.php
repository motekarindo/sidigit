<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $companyName = config('app.name', 'Percetakan');

        Branch::updateOrCreate(
            ['is_main' => true],
            [
                'name' => $companyName . ' (Headquarter)',
                'address' => config('app.company_address', 'Jalan Raya Leuwiliang - Jasinga'),
                'phone' => config('app.company_phone', '081212656699'),
                'email' => config('mail.from.address', 'cetak@example.com'),
            ]
        );
    }
}
