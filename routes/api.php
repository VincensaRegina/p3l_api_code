<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', 'Api\AuthController@login');

/////Mobile
//Tampil menu
Route::get('menuPublic', 'Api\MenuController@index');
Route::get('menuJenis/{jenis}', 'Api\MenuController@getMenuBerdasarkanJenis');
//Bahan dan Remaining Stock
Route::post('updateRSMobile/{id_bahan}/{jenis}', 'Api\RemainingStockController@updateRSMobile');
Route::post('updateStokMobile/{id_bahan}/{jenis}', 'Api\BahanController@updateStokMobile');
Route::post('keluarBahan/{id}', 'Api\MenuController@cekStokBahan');
//Pesanan
Route::post('pesanan', 'Api\PesananController@store');
Route::post('pesanan/{id}', 'Api\PesananController@updateQty');
Route::post('locked/{id}', 'Api\PesananController@updateLocked');
Route::get('cekMenuCart/{id_reservasi}/{id_menu}', 'Api\PesananController@cekMenudiCart');
Route::get('pesananSpecificCustomer/{id_reservasi}', 'Api\PesananController@indexSpecificCustomer');
Route::delete('pesanan/{id}', 'Api\PesananController@destroy');
//Riwayat
Route::post('riwayatKeluarMobile', 'Api\RiwayatKeluarController@store');
//Transaksi
Route::post('transaksiMobile', 'Api\TransaksiController@storeMobile');
Route::get('countTransaksiMobile', 'Api\TransaksiController@countTransaksi');

Route::middleware('auth:api')->get('/user', function (Request $request) {
  return $request->user();
});

Route::group(['middleware' => ['auth:api']], function () {

  //Bahan
  Route::get('bahan', 'Api\BahanController@index');
  Route::get('bahan/{id}', 'Api\BahanController@show');
  Route::post('bahan', 'Api\BahanController@store');
  Route::post('tambahStok/{id}', 'Api\BahanController@tambahStok');
  Route::post('buangStok/{id}', 'Api\BahanController@buangStok');
  Route::post('bahan/{id}', 'Api\BahanController@update');
  Route::delete('bahan/{id}', 'Api\BahanController@destroy');

  //Customer
  Route::get('customer', 'Api\CustomerController@index');
  Route::get('customer/{id}', 'Api\CustomerController@show');
  Route::get('getIdCustomerLast', 'Api\CustomerController@getIdCustomerLast');
  Route::post('customer', 'Api\CustomerController@store');
  Route::post('customer/{id}', 'Api\CustomerController@update');
  Route::delete('customer/{id}', 'Api\CustomerController@destroy');

  //Kartu
  Route::get('kartu', 'Api\KartuController@index');
  Route::get('kartu/{id}', 'Api\KartuController@show');
  Route::get('getIdKartuLast', 'Api\KartuController@getIdKartuLast');
  Route::post('kartu', 'Api\KartuController@store');
  Route::post('kartu/{id}', 'Api\KartuController@update');
  Route::delete('kartu/{id}', 'Api\KartuController@destroy');

  //Karyawan 
  // Route::get('detailsKaryawan', 'Api\AuthController@details')->middleware('verified');
  Route::get('karyawan/{id}', 'Api\AuthController@show');
  Route::post('updateKaryawan/{id}', 'Api\AuthController@update')->middleware('verified');
  Route::get('getKaryawan', 'Api\AuthController@getAllUsers');
  Route::post('register', 'Api\AuthController@register');
  Route::post('nonAktifKaryawan/{id}', 'Api\AuthController@nonAktif');
  Route::post('changePassword/{id}', 'Api\AuthController@changePassword');
  Route::post('hashCheck/{id}', 'Api\AuthController@hashCheck');
  Route::get('logout', 'Api\AuthController@logout');

  //Laporan 
  Route::get('laporanStokCustom/{from}/{to}/{karyawan}', 'Api\LaporanController@laporanStokCustom');
  Route::get('laporanStokBT/{idMenu}/{tahun}/{bulan}/{karyawan}', 'Api\LaporanController@laporanStokBT');
  Route::get('laporanPIM1/{tahun}/{bulan}/{karyawan}', 'Api\LaporanController@laporanPIM1');
  Route::get('laporanPIM2/{tahun}/{karyawan}', 'Api\LaporanController@laporanPIM2');
  Route::get('laporanPendapatanBulanan/{tahun}/{karyawan}', 'Api\LaporanController@laporanPendapatanBulanan');
  Route::get('laporanPendapatanTahunan/{from}/{to}/{karyawan}', 'Api\LaporanController@laporanPendapatanTahunan');
  Route::get('laporanPengeluaranBulanan/{tahun}/{karyawan}', 'Api\LaporanController@laporanPengeluaranBulanan');
  Route::get('laporanPengeluaranTahunan/{from}/{to}/{karyawan}', 'Api\LaporanController@laporanPengeluaranTahunan');

  //Meja
  Route::get('meja', 'Api\MejaController@index');
  Route::get('meja/{id}', 'Api\MejaController@show');
  Route::get('mejaKosong/{tgl_reservasi}/{sesi}', 'Api\MejaController@getMejaKosong');
  Route::get('getIdMeja/{noMeja}', 'Api\MejaController@getIdMeja');
  Route::get('mejaTransaksi', 'Api\MejaController@readMejaTransaksi');
  Route::post('meja', 'Api\MejaController@store');
  Route::post('meja/{id}', 'Api\MejaController@update');
  Route::post('updateMejaStatus/{id}', 'Api\MejaController@updateMejaStatus');
  Route::delete('meja/{id}', 'Api\MejaController@destroy');

  //Menu
  Route::get('menu', 'Api\MenuController@index');
  Route::get('menu/{id}', 'Api\MenuController@show');
  Route::post('menu', 'Api\MenuController@store');
  Route::post('menu/{id}', 'Api\MenuController@update');
  Route::delete('menu/{id}', 'Api\MenuController@destroy');

  //Pesanan
  Route::get('pesananRiwayat', 'Api\PesananController@indexRiwayat');
  Route::get('pesananOngoing', 'Api\PesananController@indexOngoing');
  Route::get('pesananAll', 'Api\PesananController@indexAll');
  Route::get('pesanan/{id}', 'Api\PesananController@show');
  Route::post('statusPesanan/{id}', 'Api\PesananController@updateStatus');

  //Remaining Stock
  Route::get('remainingStock', 'Api\RemainingStockController@index');
  Route::get('remainingStock/{id}', 'Api\RemainingStockController@show');
  Route::get('getIdRemainingStock/{tgl}/{idBahan}', 'Api\RemainingStockController@getIdRemainingStock');
  Route::post('remainingStock', 'Api\RemainingStockController@store');
  Route::post('remainingTambahStok/{id}', 'Api\RemainingStockController@updateTambahStok');
  Route::post('remainingBuangStok/{id}', 'Api\RemainingStockController@updateBuangStok');

  //Reservasi
  Route::get('reservasi', 'Api\ReservasiController@index');
  Route::get('reservasi/{id}', 'Api\ReservasiController@show');
  Route::get('reservasiOngoingMeja/{noMeja}', 'Api\ReservasiController@readReservasiOngoingMeja');
  Route::get('reservasiFinished/{idReservasi}', 'Api\ReservasiController@readReservasiFinished');
  Route::post('reservasi', 'Api\ReservasiController@store');
  Route::post('reservasi/{id}', 'Api\ReservasiController@update');
  Route::post('statusReservasi/{id}', 'Api\ReservasiController@updateStatus');
  Route::delete('reservasi/{id}', 'Api\ReservasiController@destroy');

  //Riwayat Keluar
  Route::get('riwayatKeluar', 'Api\RiwayatKeluarController@index');
  Route::get('riwayatKeluar/{id}', 'Api\RiwayatKeluarController@show');
  Route::post('riwayatKeluar', 'Api\RiwayatKeluarController@store');
  Route::post('riwayatKeluar/{id}', 'Api\RiwayatKeluarController@update');
  Route::delete('riwayatKeluar/{id}', 'Api\RiwayatKeluarController@destroy');

  //Riwayat Masuk
  Route::get('riwayatMasuk', 'Api\RiwayatMasukController@index');
  Route::get('riwayatMasuk/{id}', 'Api\RiwayatMasukController@show');
  Route::post('riwayatMasuk', 'Api\RiwayatMasukController@store');
  Route::post('riwayatMasuk/{id}', 'Api\RiwayatMasukController@update');
  Route::delete('riwayatMasuk/{id}', 'Api\RiwayatMasukController@destroy');

  //Transaksi
  Route::get('transaksi', 'Api\TransaksiController@index');
  Route::get('transaksi/{id}', 'Api\TransaksiController@show');
  Route::get('countTransaksi', 'Api\TransaksiController@countTransaksi');
  Route::post('transaksi', 'Api\TransaksiController@store');
  Route::post('updateTransaksi/{id_reservasi}', 'Api\TransaksiController@updateTransaksi');
});
