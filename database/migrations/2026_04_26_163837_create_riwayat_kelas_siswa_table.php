<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas_siswa', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('kelas_lama_id')->nullable();
            $table->unsignedBigInteger('kelas_baru_id');

            $table->timestamp('tanggal_pindah')->useCurrent();

            $table->string('created_user')->nullable();

            $table->timestamps();

            // Relasi
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('kelas_lama_id')->references('id')->on('kelas')->nullOnDelete();
            $table->foreign('kelas_baru_id')->references('id')->on('kelas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas_siswa');
    }
};