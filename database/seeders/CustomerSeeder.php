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
            'name' => 'Customer Umum',
            'address' => 'Alamat customer belum diisi.',
            'phone_number' => '0800000000',
            'member_type' => 'umum',
        ]);
    }
}
