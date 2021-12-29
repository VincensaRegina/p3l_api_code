<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reservasi')->constrained('reservasi');
            $table->foreignId('id_karyawan')->constrained('karyawan');
            $table->foreignId('id_kartu')->constrained('kartu');
            $table->string('no_transaksi');
            $table->date('tgl_bayar');
            $table->time('jam_bayar');
            $table->string('tipe_bayar');
            $table->decimal('subtotal',10,2);
            $table->decimal('total',10,2);
            $table->string('kode_edc');
            $table->string('status');
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
        Schema::dropIfExists('transaksi');
    }
}
