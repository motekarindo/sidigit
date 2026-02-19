<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil role yang dibutuhkan
        $adminRole = Role::where('name', 'Administrator')->first()
            ?? Role::where('slug', 'administrator')->first();
        $userRole = Role::where('name', 'User')->first()
            ?? Role::where('slug', 'user')->first();

        if (!$adminRole || !$userRole) {
            return;
        }

        // 2. Buat User Admin dan berikan peran Admin
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->roles()->attach($adminRole->id);

        // 3. Buat 10 User biasa dan berikan peran User menggunakan Factory Callback
        User::factory(10)->afterCreating(function (User $user) use ($userRole) {
            $user->roles()->attach($userRole->id);
        })->create();
    }
}
