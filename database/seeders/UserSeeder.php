<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil role yang dibutuhkan
        $superadminRole = Role::where('name', 'Superadmin')->first()
            ?? Role::where('slug', 'superadmin')->first();
        $ownerRole = Role::where('name', 'Owner')->first()
            ?? Role::where('slug', 'owner')->first();
        $kasirRole = Role::where('name', 'Kasir')->first()
            ?? Role::where('slug', 'kasir')->first();

        if (!$superadminRole) {
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

        // 2. Buat/update user superadmin dan berikan role superadmin
        $superadminUser = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Superadmin User',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'branch_id' => $mainBranch->id,
            ]
        );
        $superadminUser->roles()->syncWithoutDetaching([$superadminRole->id]);
        $superadminUser->branches()->syncWithoutDetaching([$mainBranch->id]);

        // 3. Buat/update user owner default (hak akses tertinggi tenant)
        if ($ownerRole) {
            $ownerUser = User::updateOrCreate(
                ['email' => 'owner@gmail.com'],
                [
                    'name' => 'owner',
                    'username' => 'owner',
                    'password' => Hash::make('password'),
                    'branch_id' => $mainBranch->id,
                ]
            );

            $ownerUser->roles()->syncWithoutDetaching([$ownerRole->id]);
            $ownerUser->branches()->syncWithoutDetaching([$mainBranch->id]);
        }

        // 4. Buat/update user kasir default
        if ($kasirRole) {
            $kasirUser = User::updateOrCreate(
                ['email' => 'kasir@gmail.com'],
                [
                    'name' => 'kasir',
                    'username' => 'kasir',
                    'password' => Hash::make('password'),
                    'branch_id' => $mainBranch->id,
                ]
            );

            $kasirUser->roles()->syncWithoutDetaching([$kasirRole->id]);
            $kasirUser->branches()->syncWithoutDetaching([$mainBranch->id]);
        }
    }
}
