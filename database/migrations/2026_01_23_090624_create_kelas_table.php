<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);
            $table->string('tahun_ajaran', 20);
            $table->timestamps();

            $table->string('created_user', 100)->nullable();
            $table->string('updated_user', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
