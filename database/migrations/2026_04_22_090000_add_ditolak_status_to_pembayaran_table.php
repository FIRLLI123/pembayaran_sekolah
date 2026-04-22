<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pembayaran
            MODIFY status ENUM('lunas', 'cicil', 'pending', 'ditolak') DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE pembayaran SET status = 'pending' WHERE status = 'ditolak'
        ");

        DB::statement("
            ALTER TABLE pembayaran
            MODIFY status ENUM('lunas', 'cicil', 'pending') DEFAULT 'pending'
        ");
    }
};
