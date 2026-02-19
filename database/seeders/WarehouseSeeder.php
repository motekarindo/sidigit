<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Gudang Utama',
                'location_lat' => null,
                'location_lng' => null,
                'description' => 'Gudang utama.',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            $exists = DB::table('mst_warehouses')
                ->whereRaw('LOWER(name) = ?', [Str::lower($warehouse['name'])])
                ->exists();

            if (!$exists) {
                DB::table('mst_warehouses')->insert([
                    'name' => $warehouse['name'],
                    'location_lat' => $warehouse['location_lat'],
                    'location_lng' => $warehouse['location_lng'],
                    'description' => $warehouse['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
