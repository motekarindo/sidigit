<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'approve')->update(['status' => 'approval']);
        DB::table('order_status_logs')->where('status', 'approve')->update(['status' => 'approval']);
    }

    public function down(): void
    {
        // Tidak mengembalikan perubahan agar status tetap konsisten.
    }
};
