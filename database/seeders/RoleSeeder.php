<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gunakan updateOrCreate agar tidak duplikat jika seeder dijalankan ulang
        Role::updateOrCreate(
            ['name' => 'Administrator'],
            ['name' => 'Administrator']
        );

        Role::updateOrCreate(
            ['name' => 'User'],
            ['name' => 'User']
        );
    }
}
