<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16); // material | general
            $table->foreignId('material_id')->nullable()->constrained('mst_materials')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('mst_suppliers')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('mst_units')->nullOnDelete();
            $table->decimal('qty', 12, 2)->nullable();
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->decimal('qty_base', 12, 2)->nullable();
            $table->decimal('unit_cost_base', 14, 2)->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('payment_method', 32)->default('cash');
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
