<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFkTagihanToPembayaran extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
    $table->unsignedBigInteger('tagihan_id')->nullable();

    $table->foreign('tagihan_id')
          ->references('id')
          ->on('tagihan')
          ->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            //
        });
    }
}
