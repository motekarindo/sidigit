<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrator')->first()
            ?? Role::where('slug', 'administrator')->first();
        $userRole = Role::where('name', 'User')->first()
            ?? Role::where('slug', 'user')->first();

        if (!$adminRole || !$userRole) {
            return;
        }

        // --- Hak Akses Menu ---
        // Admin dapat semua menu
        $adminRole->menus()->sync(Menu::pluck('id'));
        // User dapat menu tertentu
        $userMenus = Menu::whereIn('name', ['Dashboard', 'Manajemen Produk', 'Produk', 'Kategori'])->pluck('id');
        $userRole->menus()->sync($userMenus);

        // --- Hak Akses Permission ---
        // Admin dapat semua permission
        $adminRole->permissions()->sync(Permission::pluck('id'));
        // User dapat permission tertentu
        $userPermissions = Permission::whereIn('slug', [
            'product.view',
            'product.export',
            'category.view',
            'category.export'
        ])->pluck('id');
        $userRole->permissions()->sync($userPermissions);
    }
}
