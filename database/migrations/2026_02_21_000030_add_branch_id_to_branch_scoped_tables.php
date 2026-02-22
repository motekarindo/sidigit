<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('branches')) {
            $hasMain = DB::table('branches')->where('id', 1)->exists();
            if (!$hasMain) {
                DB::table('branches')->insert([
                    'id' => 1,
                    'name' => (string) config('app.name', 'Percetakan') . ' (Headquarter)',
                    'address' => config('app.company_address', 'Alamat belum diatur.'),
                    'phone' => config('app.company_phone', '-'),
                    'email' => config('mail.from.address', '-'),
                    'is_main' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $addBranchId = function (string $tableName): void {
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('branch_id')->default(1)->after('id')->constrained('branches')->cascadeOnDelete();
                });
            }
        };

        $addBranchId('orders');
        $addBranchId('payments');
        $addBranchId('expenses');
        $addBranchId('stock_movements');
        $addBranchId('mst_customers');
        $addBranchId('mst_suppliers');
        $addBranchId('mst_materials');
        $addBranchId('mst_products');
        $addBranchId('mst_categories');
        $addBranchId('mst_units');
        $addBranchId('mst_warehouses');
        $addBranchId('mst_employees');
        $addBranchId('employee_attendances');
        $addBranchId('employee_loans');
        $addBranchId('mst_bank_accounts');
        $addBranchId('finishes');
    }

    public function down(): void
    {
        $dropBranchId = function (string $tableName): void {
            if (Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('branch_id');
                });
            }
        };

        $dropBranchId('finishes');
        $dropBranchId('mst_bank_accounts');
        $dropBranchId('employee_loans');
        $dropBranchId('employee_attendances');
        $dropBranchId('mst_employees');
        $dropBranchId('mst_warehouses');
        $dropBranchId('mst_units');
        $dropBranchId('mst_categories');
        $dropBranchId('mst_products');
        $dropBranchId('mst_materials');
        $dropBranchId('mst_suppliers');
        $dropBranchId('mst_customers');
        $dropBranchId('stock_movements');
        $dropBranchId('expenses');
        $dropBranchId('payments');
        $dropBranchId('orders');
    }
};
