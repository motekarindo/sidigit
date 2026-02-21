<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
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

        $mainBranch = Branch::where('is_main', true)->first();
        if (!$mainBranch) {
            $mainBranch = Branch::create([
                'name' => config('app.name', 'Percetakan') . ' (Induk)',
                'address' => config('app.company_address', 'Alamat belum diatur.'),
                'phone' => config('app.company_phone', '-'),
                'email' => config('mail.from.address', '-'),
                'is_main' => true,
            ]);
        }

        // 2. Buat/User admin dan berikan peran Admin
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'branch_id' => $mainBranch->id,
            ]
        );
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        $adminUser->branches()->syncWithoutDetaching([$mainBranch->id]);

        // 3. Buat 10 User biasa dan berikan peran User menggunakan Factory Callback
        User::factory(5)->afterCreating(function (User $user) use ($userRole, $mainBranch) {
            $user->roles()->attach($userRole->id);
            $user->update(['branch_id' => $mainBranch->id]);
            $user->branches()->syncWithoutDetaching([$mainBranch->id]);
        })->create();
    }
}
