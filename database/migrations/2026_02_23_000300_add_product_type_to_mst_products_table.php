<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_products', function (Blueprint $table) {
            $table->string('product_type', 20)->default('goods')->after('name');
        });

        $productIdsWithMaterial = DB::table('mst_product_materials')
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('product_id')
            ->all();

        if (!empty($productIdsWithMaterial)) {
            DB::table('mst_products')
                ->whereIn('id', $productIdsWithMaterial)
                ->update(['product_type' => 'goods']);

            DB::table('mst_products')
                ->whereNotIn('id', $productIdsWithMaterial)
                ->update(['product_type' => 'service']);
        }
    }

    public function down(): void
    {
        Schema::table('mst_products', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
};
