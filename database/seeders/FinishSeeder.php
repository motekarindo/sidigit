<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinishSeeder extends Seeder
{
    public function run(): void
    {
        $finishes = [
            ['name' => 'Laminasi Doff', 'price' => 5000, 'is_active' => true],
            ['name' => 'Laminasi Glossy', 'price' => 5000, 'is_active' => true],
            ['name' => 'Eyelet', 'price' => 3000, 'is_active' => true],
            ['name' => 'Potong', 'price' => 2000, 'is_active' => true],
            ['name' => 'Mata Ayam', 'price' => 0, 'is_active' => true],
            ['name' => 'Tanpa Finishing', 'price' => 0, 'is_active' => true],
        ];

        foreach ($finishes as $finish) {
            $exists = DB::table('finishes')
                ->whereRaw('LOWER(name) = ?', [strtolower($finish['name'])])
                ->exists();

            if (!$exists) {
                DB::table('finishes')->insert([
                    'name' => $finish['name'],
                    'price' => $finish['price'],
                    'is_active' => $finish['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
