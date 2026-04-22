<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipePeriodeToJenisPembayaranTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
{
    Schema::table('jenis_pembayaran', function (Blueprint $table) {
        $table->enum('tipe', ['rutin', 'insidental'])->default('insidental');
        $table->enum('periode', ['bulanan', 'tahunan'])->nullable();
    });
}

public function down(): void
{
    Schema::table('jenis_pembayaran', function (Blueprint $table) {
        $table->dropColumn(['tipe', 'periode']);
    });
}
}
