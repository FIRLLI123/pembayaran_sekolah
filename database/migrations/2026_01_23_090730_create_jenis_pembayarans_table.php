<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jenis_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pembayaran', 100);
            $table->integer('nominal_default');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->string('created_user', 100)->nullable();
            $table->string('updated_user', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pembayaran');
    }
};
