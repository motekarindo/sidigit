<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Supplier Umum',
                'on_behalf' => null,
                'address' => 'Alamat belum diisi.',
                'industry' => 'Umum',
                'phone_number' => '0800000000',
                'email' => 'supplier@example.com',
                'rekening_number' => null,
            ],
        ];

        foreach ($suppliers as $supplier) {
            $exists = DB::table('mst_suppliers')
                ->whereRaw('LOWER(name) = ?', [Str::lower($supplier['name'])])
                ->exists();

            if (!$exists) {
                DB::table('mst_suppliers')->insert([
                    'name' => $supplier['name'],
                    'on_behalf' => $supplier['on_behalf'],
                    'address' => $supplier['address'],
                    'industry' => $supplier['industry'],
                    'phone_number' => $supplier['phone_number'],
                    'email' => $supplier['email'],
                    'rekening_number' => $supplier['rekening_number'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
