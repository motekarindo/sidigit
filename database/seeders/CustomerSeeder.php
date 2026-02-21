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
            'address' => 'Cibatok',
            'phone_number' => '081212656699',
            'member_type' => 'umum',
        ]);
    }
}
