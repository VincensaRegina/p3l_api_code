<?php

namespace App\Http\Controllers\Api;

use App\Bahan;
use App\Menu;
use App\RemainingStock;
use App\RiwayatMasuk;
use App\Transaksi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade as PDF;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class LaporanController extends Controller
{
  //------------LAPORAN PENJUALAN ITEM MENU BULAN TERTENTU------------------------//
  public function laporanPIM1($tahun, $bulan, $karyawan)
  {
    // $bulan = 5;
    // $tahun = 2021;
    DB::statement(DB::raw('set @rownum = 0'));

    $data = Menu::leftJoin("pesanan AS p", function ($join) {
      $join->on('p.id_menu', '=', 'menu.id');
    })
      ->leftJoin("reservasi AS r", function ($join) use ($bulan, $tahun) {
        $join->on('r.id', '=', 'p.id_reservasi')
          ->whereMonth("r.tgl_reservasi", '=', $bulan)
          ->whereYear("r.tgl_reservasi", '=', $tahun);
      })
      ->select(
        DB::raw("@rownum := @rownum + 1 as No"),
        "menu.jenis as jenis",
        "menu.nama as nama_menu",
        "menu.unit as unit",
        DB::raw("max(case when p.qty is not NULL AND month(r.tgl_reservasi) = $bulan AND year(r.tgl_reservasi) = $tahun
                                      then p.qty else 0
                                      end) AS penjualan_harian_tertinggi"),
        DB::raw("sum(case when p.qty is not null and month(r.tgl_reservasi)= $bulan and year(r.tgl_reservasi)= $tahun 
                                      then p.qty else 0 
                                      end) as total_penjualan")
      )
      ->groupBy("nama_menu", 'jenis', 'unit')
      ->orderBy("No")
      ->get();

    if (count($data) <= 0) {
      return response([
        'message' => 'Tidak ada data!',
        'data' => null,
      ], 404);
    }

    // return $data;
    //Array berisi jenis makanan
    $jenis = ['Makanan Utama', 'Makanan Side Dish', 'Minuman'];

    //Dapet bulan yg sudah terformat
    $bulanString = Carbon::create($tahun, $bulan)->translatedFormat('F');

    //Load PDF
    $pdf = PDF::loadView('laporanPIM1', [
      'no' => 1,
      'data' => $data,
      'jenis' => $jenis,
      'tahun' => $tahun,
      'bulan' => $bulanString,
      'karyawan' => $karyawan,
    ]);
    return $pdf->stream();
  }

  //------------LAPORAN PENJUALAN ITEM MENU BULAN ALL------------------------//
  public function laporanPIM2($tahun, $karyawan)
  {
    // $tahun = 2021;
    DB::statement(DB::raw('set @rownum = 0'));

    $data = Menu::leftJoin("pesanan AS p", function ($join) {
      $join->on('p.id_menu', '=', 'menu.id');
    })
      ->leftJoin("reservasi AS r", function ($join) use ($tahun) {
        $join->on('r.id', '=', 'p.id_reservasi')
          ->whereYear("r.tgl_reservasi", '=', $tahun);
      })
      ->select(
        DB::raw("@rownum := @rownum + 1 as No"),
        "menu.jenis as jenis",
        "menu.nama as nama_menu",
        "menu.unit as unit",
        DB::raw("max(case when p.qty is not NULL AND year(r.tgl_reservasi) = $tahun
                                      then p.qty else 0
                                      end) AS penjualan_harian_tertinggi"),
        DB::raw("sum(case when p.qty is not null and year(r.tgl_reservasi)= $tahun 
                                      then p.qty else 0 
                                      end) as total_penjualan")
      )
      ->groupBy("nama_menu", 'jenis', 'unit')
      ->orderBy("No")
      ->get();

    if (count($data) <= 0) {
      return response([
        'message' => 'Tidak ada data!',
        'data' => null,
      ], 404);
    }

    //Array berisi jenis makanan
    $jenis = ['Makanan Utama', 'Makanan Side Dish', 'Minuman'];

    //Load PDF
    $pdf = PDF::loadView('laporanPIM2', [
      'no' => 1,
      'data' => $data,
      'jenis' => $jenis,
      'tahun' => $tahun,
      'karyawan' => $karyawan,
    ]);
    return $pdf->stream();
  }


  //------------LAPORAN STOK CUSTOM------------------------//
  public function laporanStokCustom($from, $to, $karyawan)
  {
    // $from = date('2021-05-09');
    // $to = date('2021-05-20');

    //Query untuk dapat kolom incoming stock dan remaining stock
    DB::statement(DB::raw('set @rownum = 0'));
    $incomingStock = Bahan::leftJoin('remaining_stock as rs', function ($join) use ($from, $to) {
      $join->on('rs.id_bahan', '=', 'bahan.id')
        ->whereBetween('rs.tgl', [$from, $to]);
    })
      ->join('menu as mn', function ($join) {
        $join->on('mn.id_bahan', '=', 'bahan.id')
          ->whereNull('mn.deleted_at');
      })
      ->leftJoin("riwayat_masuk as rm", function ($join) {
        $join->on('rm.tgl_masuk', '=', 'rs.tgl')
          ->on("rm.id_bahan", '=', 'bahan.id');
      })
      ->select(
        // DB::raw('@rownum := @rownum + 1 as no'),
        'bahan.id as id_bahan',
        'bahan.nama_bahan as nama_bahan',
        'mn.jenis as jenis_menu',
        'bahan.unit as unit',
        DB::raw('sum(case when rm.stok_masuk is not null
                     then rm.stok_masuk else 0 end) as incoming_stock'),
      )

      ->groupBy('id_bahan', 'unit', 'nama_bahan', 'jenis_menu')
      ->orderBy('id_bahan')
      ->get();
    //return $incomingStock;

    $rs = Bahan::leftJoin('remaining_stock as rs', function ($join) use ($from, $to) {
      $join->on('rs.id_bahan', '=', 'bahan.id')
        ->whereBetween('rs.tgl', [$from, $to]);
    })
      ->select(
        'bahan.id as id_bahan',
        DB::raw('sum(case when rs.stok is not null
                then rs.stok else 0 end) as remaining_stock'),
      )
      ->groupBy('bahan.id')
      ->get();
    // return $rs;
    //Query untuk dapat kolom waste stock
    DB::statement(DB::raw('set @rownum2 = 0'));
    $wasteStock = Bahan::leftjoin('remaining_stock as rs', function ($join) use ($from, $to) {
      $join->on('rs.id_bahan', '=', 'bahan.id')
        ->whereBetween('rs.tgl', [$from, $to]);
    })
      ->join('menu as mn', function ($join) {
        $join->on('mn.id_bahan', '=', 'bahan.id')
          ->whereNull('mn.deleted_at');
      })
      ->leftJoin("riwayat_keluar as rk", function ($join) {
        $join->on('rk.tgl_keluar', '=', 'rs.tgl')
          ->on('rk.id_bahan', '=', 'bahan.id')
          ->where('rk.keterangan', '=', 'Buang');
      })
      ->select(
        'bahan.id as id_bahan',
        DB::raw("sum(case when rk.stok_keluar is not null
                      then rk.stok_keluar else 0
                      end) as waste_stock")
      )
      ->groupBy('id_bahan')
      ->orderBy('id_bahan')
      ->get();
    // return $wasteStock;
   
    //Array berisi jenis makanan
    if (count($incomingStock) <= 0) {
      return response([
        'message' => 'Tidak ada data!',
        'data' => null,
      ], 404);
    }

    $jenis = ['Makanan Utama', 'Makanan Side Dish', 'Minuman'];

    //Load PDF
    $pdf = PDF::loadView('laporanStokCustom', [
      'incomingStock' => $incomingStock,
      'wasteStock' => $wasteStock,
      'rs' => $rs,
      'no' => 0,
      'karyawan' => $karyawan,
      'from' => $from,
      'to' => $to,
      'jenis' => $jenis,
    ]);
    return $pdf->stream();
  }


  //------------LAPORAN STOK BULAN TAHUN------------------------//
  public function laporanStokBT($idMenu, $tahun, $bulan, $karyawan)
  {

    $idMenu = $idMenu;
    $tahun = $tahun;
    $bulan = $bulan;

    //Query untuk dapat kolom incoming stock dan remaining stock
    DB::statement(DB::raw('set @rownum = 0'));
    $incomingStock = Bahan::join('remaining_stock as rs', 'rs.id_bahan', '=', 'bahan.id')
      ->join("riwayat_masuk as rm", function ($join) {
        $join->on('rm.tgl_masuk', '=', 'rs.tgl')
          ->on("rm.id_bahan", '=', 'bahan.id');
      })
      ->join('menu as mn', 'mn.id_bahan', '=', 'bahan.id')
      ->select(
        DB::raw('@rownum := @rownum + 1 as no'),
        'rs.tgl as tgl',
        'rm.id_bahan as id_bahan',
        'mn.nama as nama_menu',
        'bahan.unit as unit',
        DB::raw('sum(case when rm.stok_masuk is not null
                then rm.stok_masuk else 0 end) as incoming_stock'),
        'rs.stok as remaining_stock'
      )
      ->whereMonth('rs.tgl', '=', $bulan)
      ->whereYear('rs.tgl', '=', $tahun)
      ->where('mn.id', '=', $idMenu)
      ->groupBy('tgl', 'id_bahan', 'unit', 'remaining_stock', 'mn.nama')
      ->orderBy('no')
      ->get();

    //Query untuk dapat kolom waste stock
    DB::statement(DB::raw('set @rownum2 = 0'));
    $wasteStock = Bahan::join('menu as mn', 'mn.id_bahan', '=', 'bahan.id')
      ->join('remaining_stock as rs', 'rs.id_bahan', '=', 'bahan.id')
      ->leftJoin("riwayat_keluar as rk", function ($join) use ($bulan, $tahun) {
        $join->on('rk.tgl_keluar', '=', 'rs.tgl')
          ->on('rk.id_bahan', '=', 'bahan.id')
          ->whereMonth('rs.tgl', '=', $bulan)
          ->whereYear('rs.tgl', '=', $tahun)
          ->where('rk.keterangan', '=', 'Buang');
      })
      ->select(
        DB::raw('@rownum2 := @rownum2 + 1 as no'),
        'rs.tgl as tgl',
        'bahan.id as id_bahan',
        DB::raw("sum(case when rk.stok_keluar is not null
                    then rk.stok_keluar else 0 
                    end) as waste_stock")
      )
      ->where('mn.id', '=', $idMenu)
      ->groupBy('tgl', 'id_bahan')
      ->orderBy('no')
      ->get();

    // if (count($incomingStock) <= 0) {
    //   return response([
    //     'message' => 'Tidak ada data!',
    //     'data' => null,
    //   ], 404);
    // }

    $menu = Menu::where('menu.id', $idMenu)
      ->select('menu.unit', 'menu.nama')
      ->first();

    $endOfMonth = Carbon::create($tahun, $bulan)->endOfMonth(); //dapat tanggal terakhir dalam suatu bulan yg dipake di $period (utk bates akhir)
    $period = CarbonPeriod::create($tahun . '-' . $bulan . '-01', $endOfMonth); //dapet semua bulan
    $dates = collect($period)->map(function (Carbon $date) { //collection yg ada di $period diubah semuanya jadi format tertentu
      return  $date->translatedFormat('d F Y'); //jadi bhs indo
    })->toArray();

    //array baru untuk dapet tanggal2 di remaining stock
    //untuk dibandingin dengan $dates di blade.php
    $dateRS = [];
    foreach ($incomingStock as $is) {
      //parse date
      $date = Carbon::parse($is->tgl)->translatedFormat('d F Y');
      array_push($dateRS, $date);
    }

    //dapat nama bulan tahun utk judul
    $bulanTahun = Carbon::create($tahun, $bulan)->translatedFormat('F Y');

    //Load PDF
    $pdf = PDF::loadView('laporanStokBT', [
      'incomingStock' => $incomingStock,
      'wasteStock' => $wasteStock,
      'no' => 0,
      'dates' => $dates,
      'dateRS' => $dateRS,
      'karyawan' => $karyawan,
      'menu' => $menu,
      'bulanTahun' => $bulanTahun,

    ]);
    return $pdf->stream();
  }

  //------------LAPORAN PENDAPATAN BULANAN------------------------//
  public function laporanPendapatanBulanan($tahun, $karyawan)
  {
    DB::statement(DB::raw('set @rownum = 0'));
    $data = Menu::join('pesanan as p', function ($join) {
      $join->on('p.id_menu', '=', 'menu.id')
        ->where('p.locked', 'yes');
    })
      ->join('reservasi as rv', 'rv.id', '=', 'p.id_reservasi')
      ->join('transaksi as tr', function ($join) use ($tahun) {
        $join->on('tr.id_reservasi', '=', 'rv.id')
          ->where('tr.status', 'Lunas')
          ->whereYear('tr.tgl_bayar', '=', $tahun);
      })
      ->select(
        DB::raw('@rownum := @rownum + 1 as no'),
        DB::raw('month(tr.tgl_bayar) as month'),
        DB::raw("sum(case when p.subtotal is not null AND menu.jenis = 'Makanan Utama'
                  then p.subtotal else 0 
                  end) as makanan_utama"),
        DB::raw("sum(case when p.subtotal is not null AND menu.jenis = 'Makanan Side Dish'
                  then p.subtotal else 0 
                  end) as makanan_side_dish"),
        DB::raw("sum(case when p.subtotal is not null AND menu.jenis = 'Minuman'
                  then p.subtotal else 0 
                  end) as minuman"),
      )
      ->groupBy('month')
      ->get();

    $totalPendapatan = Transaksi::where('transaksi.status', 'Lunas')
      ->whereYear('transaksi.tgl_bayar', '=', $tahun)
      ->select(
        DB::raw('@rownum := @rownum + 1 as no'),
        DB::raw('month(transaksi.tgl_bayar) as month'),
        DB::raw("sum(case when transaksi.total is not null
                  then transaksi.total else 0 
                  end) as total_pendapatan"),
      )
      ->groupBy('month')
      ->get();

    //Generate month dari januari sampe desember
    $period = CarbonPeriod::create($tahun . '-01-01', '1 month', $tahun . '-12-31'); //dapet semua tanggal
    $months = collect($period)->map(function (Carbon $date) { //collection yg ada di $period diubah semuanya jadi format tertentu
      return  $date->translatedFormat('F'); //jadi bhs indo
    })->toArray();

    //array baru untuk dapet tanggal bayar di transaksi
    //untuk dibandingin dengan $months di blade.php
    $bulanBayar = [];
    foreach ($data as $d) {
      //parse date
      $date = Carbon::parse($tahun . '-' . $d->month . '-01')->translatedFormat('F');
      array_push($bulanBayar, $date);
    }

    //Load PDF
    $pdf = PDF::loadView('laporanPendapatanBulanan', [
      'data' => $data,
      'totalPendapatan' => $totalPendapatan,
      'no' => 0,
      'months' => $months,
      'bulanBayar' => $bulanBayar,
      'karyawan' => $karyawan,
      'tahun' => $tahun
    ]);
    return $pdf->stream();
  }


  //------------LAPORAN PENDAPATAN TAHUNAN------------------------//
  public function laporanPendapatanTahunan($from, $to, $karyawan)
  {
    DB::statement(DB::raw('set @rownum = 0'));
    $data = Menu::join('pesanan as p', function ($join) {
      $join->on('p.id_menu', '=', 'menu.id')
        ->where('p.locked', 'yes');
    })
      ->join('reservasi as rv', 'rv.id', '=', 'p.id_reservasi')
      ->join('transaksi as tr', function ($join) use ($from, $to) {
        $join->on('tr.id_reservasi', '=', 'rv.id')
          ->where('tr.status', 'Lunas')
          ->whereBetween(DB::raw('year(tr.tgl_bayar)'), [$from, $to]);
      })
      ->select(
        DB::raw('@rownum := @rownum + 1 as no'),
        DB::raw('year(tr.tgl_bayar) as year'),
        DB::raw("sum(case when p.subtotal is not null AND menu.jenis = 'Makanan Utama'
                  then p.subtotal else 0 
                  end) as makanan_utama"),
        DB::raw("sum(case when p.subtotal is not null AND menu.jenis = 'Makanan Side Dish'
                  then p.subtotal else 0 
                  end) as makanan_side_dish"),
        DB::raw("sum(case when p.subtotal is not null AND menu.jenis = 'Minuman'
                  then p.subtotal else 0 
                  end) as minuman"),
      )
      ->groupBy('year')
      ->get();

    // return $data;
    $totalPendapatan = Transaksi::where('transaksi.status', 'Lunas')
      ->whereBetween(DB::raw('year(transaksi.tgl_bayar)'), [$from, $to])
      ->select(
        DB::raw('@rownum := @rownum + 1 as no'),
        DB::raw('year(transaksi.tgl_bayar) as year'),
        DB::raw("sum(case when transaksi.total is not null
                  then transaksi.total else 0 
                  end) as total_pendapatan"),
      )
      ->groupBy('year')
      ->get();
    //  return $totalPendapatan;

    if (count($data) <= 0) {
      return response([
        'message' => 'Tidak ada data!',
        'data' => null,
      ], 404);
    }

    //Generate year dari range year yg dikasih
    $period = CarbonPeriod::create($from . '-01-01', '12 month', $to . '-12-31'); //dapet semua tanggal
    $years = collect($period)->map(function (Carbon $date) { //collection yg ada di $period diubah semuanya jadi format tertentu
      return  $date->translatedFormat('Y'); //jadi bhs indo
    })->toArray();

    //array baru untuk dapet tahun bayar di transaksi
    //untuk dibandingin dengan $years di blade.php
    $tahunBayar = [];
    foreach ($data as $d) {
      //parse date
      $date = Carbon::parse($d->year . '-01-01')->translatedFormat('Y');
      array_push($tahunBayar, $date);
    }

    //Load PDF
    $pdf = PDF::loadView('laporanPendapatanTahunan', [
      'data' => $data,
      'totalPendapatan' => $totalPendapatan,
      'no' => 0,
      'years' => $years,
      'tahunBayar' => $tahunBayar,
      'karyawan' => $karyawan,
      'from' => $from,
      'to' => $to,
    ]);
    return $pdf->stream();
  }


  //------------LAPORAN PENGELUARAN BULANAN------------------------//
  public function laporanPengeluaranBulanan($tahun, $karyawan)
  {
    $data = Menu::join('bahan as b', 'menu.id_bahan', '=', 'b.id')
      ->join('riwayat_masuk as rm', 'rm.id_bahan', '=', 'b.id')
      ->select(
        DB::raw('month(rm.tgl_masuk) as month'),
        DB::raw("sum(case when rm.biaya is not null AND menu.jenis = 'Makanan Utama'
                  then rm.biaya else 0 
                  end) as makanan_utama"),
        DB::raw("sum(case when rm.biaya is not null AND menu.jenis = 'Makanan Side Dish'
                  then rm.biaya else 0 
                  end) as makanan_side_dish"),
        DB::raw("sum(case when rm.biaya is not null AND menu.jenis = 'Minuman'
                  then rm.biaya else 0 
                  end) as minuman"),
      )
      ->whereYear('rm.tgl_masuk', '=', $tahun)
      ->where('menu.deleted_at', '=', NULL)
      ->groupBy('month')
      ->get();

    $totalPengeluaran = RiwayatMasuk::whereYear('riwayat_masuk.tgl_masuk', '=', $tahun)
      ->select(
        DB::raw('month(riwayat_masuk.tgl_masuk) as month'),
        DB::raw("sum(case when riwayat_masuk.biaya is not null
                  then riwayat_masuk.biaya else 0 
                  end) as total_pengeluaran"),
      )
      ->groupBy('month')
      ->get();

    if (count($data) <= 0) {
      return response([
        'message' => 'Tidak ada data!',
        'data' => null,
      ], 404);
    }
    // return $totalPengeluaran;
    //Generate month dari januari sampe desember
    $period = CarbonPeriod::create($tahun . '-01-01', '1 month', $tahun . '-12-31'); //dapet semua tanggal
    $months = collect($period)->map(function (Carbon $date) { //collection yg ada di $period diubah semuanya jadi format tertentu
      return  $date->translatedFormat('F'); //jadi bhs indo
    })->toArray();

    //array baru untuk dapet tanggal bayar di transaksi
    //untuk dibandingin dengan $months di blade.php
    $bulanBayar = [];
    foreach ($data as $d) {
      //parse date
      $date = Carbon::parse($tahun . '-' . $d->month . '-01')->translatedFormat('F');
      array_push($bulanBayar, $date);
    }

    //Load PDF
    $pdf = PDF::loadView('laporanPengeluaranBulanan', [
      'data' => $data,
      'totalPengeluaran' => $totalPengeluaran,
      'no' => 0,
      'months' => $months,
      'bulanBayar' => $bulanBayar,
      'karyawan' => $karyawan,
      'tahun' => $tahun
    ]);
    return $pdf->stream();
  }


  //------------LAPORAN PENGELUARAN TAHUNAN------------------------//
  public function laporanPengeluaranTahunan($from, $to, $karyawan)
  {
    $data = Menu::join('bahan as b', 'menu.id_bahan', '=', 'b.id')
      ->join('riwayat_masuk as rm', 'rm.id_bahan', '=', 'b.id')
      ->select(
        DB::raw('year(rm.tgl_masuk) as year'),
        DB::raw("sum(case when rm.biaya is not null AND menu.jenis = 'Makanan Utama'
                  then rm.biaya else 0 
                  end) as makanan_utama"),
        DB::raw("sum(case when rm.biaya is not null AND menu.jenis = 'Makanan Side Dish'
                  then rm.biaya else 0 
                  end) as makanan_side_dish"),
        DB::raw("sum(case when rm.biaya is not null AND menu.jenis = 'Minuman'
                  then rm.biaya else 0 
                  end) as minuman"),
      )
      ->whereBetween(DB::raw('year(rm.tgl_masuk)'), [$from, $to])
      ->where('menu.deleted_at', '=', NULL)
      ->groupBy('year')
      ->get();

    // return $data;
    $totalPengeluaran =  RiwayatMasuk::whereBetween(DB::raw('year(riwayat_masuk.tgl_masuk)'), [$from, $to])
      ->select(
        DB::raw('year(riwayat_masuk.tgl_masuk) as year'),
        DB::raw("sum(case when riwayat_masuk.biaya is not null
                then riwayat_masuk.biaya else 0 
                end) as total_pengeluaran"),
      )
      ->groupBy('year')
      ->get();

    //  return $totalPengeluaran;

    // if (count($data) <= 0) {
    //   return response([
    //     'message' => 'Tidak ada data!',
    //     'data' => null,
    //   ], 404);
    // }

    //Generate year dari range year yg dikasih
    $period = CarbonPeriod::create($from . '-01-01', '12 month', $to . '-12-31'); //dapet semua tanggal
    $years = collect($period)->map(function (Carbon $date) { //collection yg ada di $period diubah semuanya jadi format tertentu
      return  $date->translatedFormat('Y'); //jadi bhs indo
    })->toArray();

    //array baru untuk dapet tahun bayar di transaksi
    //untuk dibandingin dengan $years di blade.php
    $tahunBayar = [];
    foreach ($data as $d) {
      //parse date
      $date = Carbon::parse($d->year . '-01-01')->translatedFormat('Y');
      array_push($tahunBayar, $date);
    }

    //Load PDF
    $pdf = PDF::loadView('laporanPengeluaranTahunan', [
      'data' => $data,
      'totalPengeluaran' => $totalPengeluaran,
      'no' => 0,
      'years' => $years,
      'tahunBayar' => $tahunBayar,
      'karyawan' => $karyawan,
      'from' => $from,
      'to' => $to,
    ]);
    return $pdf->stream();
  }
}
