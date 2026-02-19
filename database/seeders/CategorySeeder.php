<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Indoor',
            'Outdoor',
            'Merchandise',
            'Mesin A3+',
        ];

        foreach ($categories as $name) {
            $exists = DB::table('mst_categories')
                ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
                ->exists();

            if (!$exists) {
                DB::table('mst_categories')->insert([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
