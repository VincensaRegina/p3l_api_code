<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiwayatMasukTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('riwayat_masuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bahan')->constrained('bahan');
            $table->date('tgl_masuk');
            $table->integer('stok_masuk');
            $table->decimal('biaya',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('riwayat_masuk');
    }
}
