<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_product_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('mst_products')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('mst_materials')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->timestamps();

            $table->unique(['product_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_product_materials');
    }
};
