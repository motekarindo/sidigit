<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinishSeeder extends Seeder
{
    public function run(): void
    {
        $finishes = [
            ['name' => 'Laminasi Doff', 'price' => 2000, 'is_active' => true],
            ['name' => 'Laminasi Glossy', 'price' => 2000, 'is_active' => true],
            ['name' => 'Potong 1/2', 'price' => 2000, 'is_active' => true],
            ['name' => 'Potong 1/3', 'price' => 2000, 'is_active' => true],
            ['name' => 'Mata Ayam', 'price' => 0, 'is_active' => true],
            ['name' => 'Varnish', 'price' => 0, 'is_active' => true],
            ['name' => 'Emboss', 'price' => 0, 'is_active' => true],
            ['name' => 'Poly', 'price' => 0, 'is_active' => true],
            ['name' => 'Pond', 'price' => 0, 'is_active' => true],
            ['name' => 'Jilid Steples', 'price' => 0, 'is_active' => true],
            ['name' => 'Jilid Lem Panas', 'price' => 0, 'is_active' => true],
            ['name' => 'Jilid Spiral', 'price' => 0, 'is_active' => true],
            ['name' => 'Jilid Soft Cover', 'price' => 0, 'is_active' => true],
            ['name' => 'Jilid Hard Cover', 'price' => 0, 'is_active' => true],
            ['name' => 'Pond', 'price' => 0, 'is_active' => true],
            ['name' => 'Rel', 'price' => 0, 'is_active' => true],
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
