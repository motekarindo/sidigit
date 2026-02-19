<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::updateOrCreate([
            'email' => 'employee@example.com',
        ], [
            'name' => 'Karyawan Utama',
            'address' => 'Alamat karyawan belum diisi.',
            'phone_number' => '0800000001',
            'salary' => 0,
            'status' => 'active',
        ]);
    }
}
