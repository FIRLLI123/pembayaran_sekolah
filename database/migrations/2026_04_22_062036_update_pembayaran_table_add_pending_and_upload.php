<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('pembayaran', function (Blueprint $table) {
        $table->string('upload_foto')->nullable()->after('keterangan');
    });

    // ubah enum pakai SQL langsung
    DB::statement("
        ALTER TABLE pembayaran 
        MODIFY status ENUM('lunas', 'cicil', 'pending') DEFAULT 'pending'
    ");
}

 public function down(): void
{
    Schema::table('pembayaran', function (Blueprint $table) {
        $table->dropColumn('upload_foto');
    });

    DB::statement("
        ALTER TABLE pembayaran 
        MODIFY status ENUM('lunas', 'cicil') DEFAULT 'lunas'
    ");
}
};