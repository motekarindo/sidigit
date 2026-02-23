<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'CM', 'is_dimension' => true],
            ['name' => 'M', 'is_dimension' => true],
            ['name' => 'M2', 'is_dimension' => true],
            ['name' => 'Rol', 'is_dimension' => true],
            ['name' => 'Lembar', 'is_dimension' => false],
            ['name' => 'Pack', 'is_dimension' => false],
            ['name' => 'PCS', 'is_dimension' => false],
            ['name' => 'Box', 'is_dimension' => false],
            ['name' => 'RIM', 'is_dimension' => false],
            ['name' => 'SET', 'is_dimension' => false],
            ['name' => 'Lusin', 'is_dimension' => false],
        ];

        foreach ($units as $unit) {
            $exists = DB::table('mst_units')
                ->whereRaw('LOWER(name) = ?', [Str::lower($unit['name'])])
                ->exists();

            if (!$exists) {
                DB::table('mst_units')->insert([
                    'name' => $unit['name'],
                    'is_dimension' => $unit['is_dimension'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
