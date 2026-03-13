<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();
            $table->foreignId('jenis_pembayaran_id')
                  ->constrained('jenis_pembayaran')
                  ->cascadeOnDelete();
            $table->date('tanggal_bayar');
            $table->integer('nominal_bayar');
            $table->enum('metode_bayar', ['cash', 'transfer'])->default('cash');
            $table->enum('status', ['lunas', 'cicil'])->default('lunas');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->string('created_user', 100)->nullable();
            $table->string('updated_user', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
