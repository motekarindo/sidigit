<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil semua seeder dalam urutan yang logis
        $this->call([
            RoleSeeder::class,           // 1. Buat Role
            MenuSeeder::class,           // 2. Buat Menu
            PermissionSeeder::class,     // 3. Buat Permission
            RolePermissionSeeder::class, // 4. Berikan Hak Akses
            UserSeeder::class,           // 5. Buat User
            UnitSeeder::class,           // 6. Master Satuan
            CategorySeeder::class,       // 7. Master Kategori
            SupplierSeeder::class,       // 8. Master Supplier
            WarehouseSeeder::class,      // 9. Master Gudang
            MaterialSeeder::class,       // 10. Master Material
            ProductSeeder::class,        // 11. Master Produk
            FinishSeeder::class,         // 12. Master Finishing
            CustomerSeeder::class,       // 13. Master Customer
            EmployeeSeeder::class,       // 14. Master Karyawan
        ]);
    }
}
