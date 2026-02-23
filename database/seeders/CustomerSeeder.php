<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::updateOrCreate([
            'email' => 'customer@example.com',
        ], [
            'name' => 'Customer Satu',
            'address' => 'Cibatok',
            'phone_number' => '0895410155551',
            'member_type' => 'umum',
        ]);

        Customer::updateOrCreate([
            'email' => 'pelanggan@example.com',
        ], [
            'name' => 'Customer Dua',
            'address' => 'Leuwiliang',
            'phone_number' => '081212656699',
            'member_type' => 'umum',
        ]);
    }
}
