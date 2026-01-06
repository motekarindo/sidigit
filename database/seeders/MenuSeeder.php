<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Dashboard
        Menu::updateOrCreate(['route_name' => 'dashboard'], ['name' => 'Dashboard', 'icon' => 'home', 'order' => 1]);

        // --- RBAC ---
        $rbac = Menu::updateOrCreate(['name' => 'RBAC'], ['icon' => 'shield', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'roles.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Role', 'icon' => 'users', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'permissions.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Permission', 'icon' => 'shield-check', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'menus.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Menu', 'icon' => 'menu', 'order' => 3]);

        // --- MANAJEMEN BARANG ---
        $itemManagement = Menu::updateOrCreate(['name' => 'Manajemen Barang'], ['icon' => 'package', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'products.index'], ['parent_id' => $itemManagement->id, 'name' => 'Produk', 'icon' => 'box', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'categories.index'], ['parent_id' => $itemManagement->id, 'name' => 'Kategori', 'icon' => 'layers', 'order' => 2]);
    }
}
