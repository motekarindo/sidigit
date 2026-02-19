<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Dashboard
        Menu::updateOrCreate(['route_name' => 'dashboard'], ['name' => 'Dashboard', 'icon' => 'bi bi-grid-fill', 'order' => 1]);

        // --- TRANSAKSI ---
        $transactions = Menu::updateOrCreate(['name' => 'Transaksi'], ['icon' => 'bi bi-receipt', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'orders.index'], ['parent_id' => $transactions->id, 'name' => 'Order', 'order' => 1]);

        // --- MANAJEMEN PRODUK ---
        $productManagement = Menu::updateOrCreate(['name' => 'Manajemen Produk'], ['icon' => 'bi bi-box-seam-fill', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'products.index'], ['parent_id' => $productManagement->id, 'name' => 'Produk', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'categories.index'], ['parent_id' => $productManagement->id, 'name' => 'Kategori', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'units.index'], ['parent_id' => $productManagement->id, 'name' => 'Satuan', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'materials.index'], ['parent_id' => $productManagement->id, 'name' => 'Bahan', 'order' => 4]);

        // --- STOK ---
        $stockMenu = Menu::updateOrCreate(['name' => 'Stok'], ['icon' => 'bi bi-boxes', 'order' => 4]);
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Stok Masuk'],
            ['route_name' => 'stocks.in', 'order' => 1]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Stok Keluar'],
            ['route_name' => 'stocks.out', 'order' => 2]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Stok Opname'],
            ['route_name' => 'stocks.opname', 'order' => 3]
        );
        Menu::updateOrCreate(
            ['parent_id' => $stockMenu->id, 'name' => 'Saldo Stok'],
            ['route_name' => 'stocks.balances', 'order' => 4]
        );

        // --- PENGELUARAN ---
        $expenseMenu = Menu::updateOrCreate(['name' => 'Pengeluaran'], ['icon' => 'bi bi-wallet2', 'order' => 5]);
        Menu::updateOrCreate(['route_name' => 'expenses.materials.index'], ['parent_id' => $expenseMenu->id, 'name' => 'Expense Bahan', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'expenses.general.index'], ['parent_id' => $expenseMenu->id, 'name' => 'Expense Umum', 'order' => 2]);

        // --- MASTER DATA ---
        $masterData = Menu::updateOrCreate(['name' => 'Master'], ['icon' => 'bi bi-layers', 'order' => 6]);
        Menu::updateOrCreate(['route_name' => 'suppliers.index'], ['parent_id' => $masterData->id, 'name' => 'Supplier', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'warehouses.index'], ['parent_id' => $masterData->id, 'name' => 'Gudang', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'customers.index'], ['parent_id' => $masterData->id, 'name' => 'Customer', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'employees.index'], ['parent_id' => $masterData->id, 'name' => 'Karyawan', 'order' => 4]);
        Menu::updateOrCreate(['route_name' => 'bank-accounts.index'], ['parent_id' => $masterData->id, 'name' => 'Rekening Bank', 'order' => 5]);
        Menu::updateOrCreate(['route_name' => 'finishes.index'], ['parent_id' => $masterData->id, 'name' => 'Finishing', 'order' => 6]);
        Menu::updateOrCreate(['route_name' => 'attendances.index'], ['parent_id' => $masterData->id, 'name' => 'Absensi', 'order' => 7]);
        Menu::updateOrCreate(['route_name' => 'employee-loans.index'], ['parent_id' => $masterData->id, 'name' => 'Kasbon', 'order' => 8]);

        // --- LAPORAN ---
        $reportMenu = Menu::updateOrCreate(['name' => 'Laporan'], ['icon' => 'bi bi-graph-up', 'order' => 7]);
        Menu::updateOrCreate(['route_name' => 'reports.sales'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Penjualan', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'reports.expenses'], ['parent_id' => $reportMenu->id, 'name' => 'Laporan Pengeluaran', 'order' => 2]);

        // --- RBAC ---
        $rbac = Menu::updateOrCreate(['name' => 'RBAC'], ['icon' => 'bi bi-shield-shaded', 'order' => 8]);
        Menu::updateOrCreate(['route_name' => 'users.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen User', 'order' => 1]);
        Menu::updateOrCreate(['route_name' => 'roles.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Role', 'order' => 2]);
        Menu::updateOrCreate(['route_name' => 'permissions.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Permission', 'order' => 3]);
        Menu::updateOrCreate(['route_name' => 'menus.index'], ['parent_id' => $rbac->id, 'name' => 'Manajemen Menu', 'order' => 4]);

        // --- LAINNYA ---
        Menu::updateOrCreate(['route_name' => 'audit-logs.index'], ['name' => 'Audit Logs', 'icon' => 'bi bi-clipboard-data', 'order' => 9]);
    }
}
