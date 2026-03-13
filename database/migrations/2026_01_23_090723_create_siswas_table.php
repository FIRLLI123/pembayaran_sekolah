<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->unique();
            $table->string('nama_siswa', 100);
            $table->foreignId('kelas_id')
                  ->constrained('kelas')
                  ->cascadeOnDelete();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();

            $table->string('created_user', 100)->nullable();
            $table->string('updated_user', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
