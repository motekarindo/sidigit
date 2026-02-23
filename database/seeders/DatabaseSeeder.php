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
            BranchSeeder::class,         // 5. Cabang Induk
            UserSeeder::class,           // 6. Buat User
            UnitSeeder::class,           // 7. Master Satuan
            CategorySeeder::class,       // 8. Master Kategori
            SupplierSeeder::class,       // 9. Master Supplier
            WarehouseSeeder::class,      // 10. Master Gudang
            MaterialSeeder::class,       // 11. Master Material
            ProductSeeder::class,        // 12. Master Produk
            FinishSeeder::class,         // 13. Master Finishing
            CustomerSeeder::class,       // 14. Master Customer
            EmployeeSeeder::class,       // 15. Master Karyawan
        ]);
    }
}
