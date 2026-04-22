<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();

            // relasi utama
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('jenis_pembayaran_id')->constrained('jenis_pembayaran')->cascadeOnDelete();

            // info tagihan
            $table->date('tanggal_tagihan');
            $table->date('jatuh_tempo')->nullable();

            // periode (untuk rutin)
            $table->integer('periode_bulan')->nullable(); // 1-12
            $table->integer('periode_tahun')->nullable();

            // nominal
            $table->integer('nominal_tagihan');
            $table->integer('sisa_tagihan');

            // status
            $table->enum('status', ['belum_bayar', 'cicil', 'lunas'])->default('belum_bayar');

            // keterangan
            $table->text('keterangan')->nullable();

            // audit
            $table->string('created_user')->nullable();
            $table->string('updated_user')->nullable();

            $table->timestamps();

            // 🚫 cegah double generate (khusus rutin)
            $table->unique([
                'siswa_id',
                'jenis_pembayaran_id',
                'periode_bulan',
                'periode_tahun'
            ], 'unique_tagihan_rutin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};