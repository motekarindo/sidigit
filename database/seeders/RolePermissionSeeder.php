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
        $kasirRole = Role::where('name', 'Kasir')->first()
            ?? Role::where('slug', 'kasir')->first();

        if (!$adminRole || !$kasirRole) {
            return;
        }

        // --- Hak Akses Menu ---
        // Admin dapat semua menu
        $adminRole->menus()->sync(Menu::pluck('id'));
        // User dapat menu tertentu
        $kasirMenu = Menu::whereIn('name', [
            'Dashboard',
            'Customer',
            'Transaksi', 'Order',
            'Manajemen Produk', 'Produk', 'Bahan', 'Kategori',
            'Stok', 'Stok Masuk', 'Stok Keluar', 'Stok Opname', 'Reservasi Stok',
            'Pengeluaran', 'Expense Bahan','Expense Umum',
            'Laporan', 'Laporan Penjualan', 'Laporan Pengeluaran',
            'Laporan Per Cabang',
            'Master', 'Supplier', 'Gudang', 'Finishing'])->pluck('id');
        $kasirRole->menus()->sync($kasirMenu);

        // --- Hak Akses Permission ---
        // Admin dapat semua permission
        $adminRole->permissions()->sync(Permission::pluck('id'));
        // Kasir dapat permission tertentu
        $kasirPermissions = Permission::whereIn('slug', [
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            'order.view', 'order.create', 'order.edit',
            'product.view', 'product.create', 'product.edit', 'product.export',
            'category.view', 'category.export',
            'material.view', 'material.create', 'material.edit',
            'stock-in.view', 'stock-in.create', 'stock-in.edit',
            'stock-out.view', 'stock-out.create', 'stock-out.edit',
            'stock-opname.view', 'stock-opname.create', 'stock-opname.edit',
            'stock-balance.view',
            'stock-reservation.view',
            'expense-material.view', 'expense-material.create', 'expense-material.edit',
            'expense-general.view', 'expense-general.create', 'expense-general.edit',
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
            'finish.view', 'finish.create', 'finish.edit', 'finish.delete',
            'report.sales.view', 'report.expense.view', 'report.branch.view',
        ])->pluck('id');
        $kasirRole->permissions()->sync($kasirPermissions);
    }
}
