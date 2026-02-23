<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_materials', function (Blueprint $table) {
            $table->decimal('roll_width_cm', 10, 2)->nullable()->after('conversion_qty');
            $table->decimal('roll_waste_percent', 5, 2)->default(0)->after('roll_width_cm');
        });

        DB::table('mst_materials')
            ->whereRaw('LOWER(name) in (?, ?)', ['albatros', 'backlite'])
            ->update([
                'roll_width_cm' => 152,
                'roll_waste_percent' => 3,
            ]);

        DB::table('mst_materials')
            ->whereRaw('LOWER(name) like ?', ['flexy%'])
            ->update([
                'roll_width_cm' => 320,
                'roll_waste_percent' => 3,
            ]);
    }

    public function down(): void
    {
        Schema::table('mst_materials', function (Blueprint $table) {
            $table->dropColumn(['roll_width_cm', 'roll_waste_percent']);
        });
    }
};
