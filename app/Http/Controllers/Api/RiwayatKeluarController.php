<?php

namespace App\Http\Controllers\Api;

use App\RiwayatKeluar;
use App\Bahan;
use App\RemainingStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RiwayatKeluarController extends Controller
{
    //Display listing of resources
    public function index()
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('riwayat_keluar')
            ->join('bahan', 'bahan.id', '=', 'riwayat_keluar.id_bahan')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no'),
                'riwayat_keluar.*',
                'bahan.nama_bahan'
            )
            ->orderByDesc('riwayat_keluar.tgl_keluar')
            ->get();

        if (count($data) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $data
            ], 200);
        } //return data semua product dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data product kosong
    }


    //Store a newly created resource in storage.
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_bahan' => 'required|numeric',
            'tgl_keluar' => 'required',
            'stok_keluar' => 'required|numeric',
            'keterangan' => 'required'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }
        if ($storeData['keterangan'] == "Keluar") {
            //cek apakah sudah ada baris bahan keluar/buang dgn tgl dan id bahan yg sama
            $cek = RiwayatKeluar::where('tgl_keluar', $storeData['tgl_keluar'])
                ->where('id_bahan', $storeData['id_bahan'])
                ->where('keterangan', $storeData['keterangan'])
                ->first();

            if (!is_null($cek)) {
                $cek->stok_keluar += $storeData['stok_keluar'];
                if ($cek->save()) {
                    return response([
                        'message' => 'Update Riwayat Keluar Success',
                        'data' => $cek
                    ], 200);
                } //return data yang telah diedit dalam bentuk json
            }
        }

        $data = RiwayatKeluar::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Riwayat Keluar Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = RiwayatKeluar::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Riwayat Keluar Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Riwayat Keluar not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $dataRK = RiwayatKeluar::find($id); //mencari data product berdasar id
        if (is_null($dataRK)) {
            return response([
                'message' => 'Riwayat Keluar Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        //Ambil data dari request dan validasi
        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok_keluar' => 'required|numeric',
        ]); //rule validasi input
        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input
        //Dimasukin ke variabel biar gampang aja
        $stokKeluarBaru = $updateData['stok_keluar'];
        // $idBahanBaru = $updateData['id_bahan'];

        //ambil data dari tabel bahan
        $dataBahanLama = Bahan::where('id', $dataRK->id_bahan)->first();
        if (is_null($dataBahanLama)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        }
        //ambil data dari tabel remaining stock
        $dataRSLama = RemainingStock::where('tgl', $dataRK->tgl_keluar)
            ->where('id_bahan', $dataRK->id_bahan)
            ->first();
        if (is_null($dataRSLama)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        }

        //JIKA ID BAHAN TIDAK DIGANTI
        // if ($dataRK->id_bahan == $idBahanBaru) {
        if ($stokKeluarBaru > $dataRK->stok_keluar) { //jika yg dibuang nambah
            $selisih = $stokKeluarBaru - $dataRK->stok_keluar;
            if ($dataBahanLama->stok < $selisih) {
                return response([
                    'message' => 'Stok bahan tidak cukup!',
                    'data' => null
                ], 400);
            } else {
                $dataBahanLama->stok -= $selisih;
                $dataRSLama->stok -= $selisih;
            }
        } else if ($stokKeluarBaru < $dataRK->stok_keluar) { //jika yg dibuang berkurang
            $selisih = $dataRK->stok_keluar - $stokKeluarBaru;
            $dataBahanLama->stok += $selisih;
            $dataRSLama->stok += $selisih;
        }
        $dataRSLama->save();
        $dataBahanLama->save();
        // }
        //JIKA ID BAHAN DIGANTI
        // else if ($dataRK->id_bahan != $idBahanBaru) {
        //     //ambil data bahan dari id bahan baru
        //     $dataBahanBaru = Bahan::where('id', $idBahanBaru)->first();
        //     if (is_null($dataBahanBaru)) {
        //         return response([
        //             'message' => 'Bahan Not Found',
        //             'data' => null
        //         ], 404);
        //     }
        //     //ambil data dari tabel remaining stock
        //     $dataRSBaru = RemainingStock::where('tgl', $dataRK->tgl_keluar)
        //         ->where('id_bahan', $idBahanBaru)
        //         ->first();
        //     if (is_null($dataRSBaru)) {
        //         return response([
        //             'message' => 'Remaining Stock Not Found',
        //             'data' => null
        //         ], 404);
        //     }
        //     if ($stokKeluarBaru > $dataRK->stok_keluar) { //jika yg dibuang nambah
        //         $selisih = $stokKeluarBaru - $dataRK->stok_keluar;
        //         $dataBahanLama->stok += $dataRK->stok_keluar;
        //         $dataRSLama->stok += $dataRK->stok_keluar;
        //         if($dataBahanBaru->stok < $stokKeluarBaru)
        //         $dataBahanBaru->stok -= $stokKeluarBaru;
        //         $dataRSBaru->stok -= $stokKeluarBaru;
        //     } else if ($stokKeluarBaru < $dataRK->stok_keluar) { //jika yg dibuang berkurang
        //         $selisih = $dataRK->stok_keluar - $stokKeluarBaru;
        //         $dataBahanLama->stok -= $dataRK->stok_keluar;
        //         $dataRSLama->stok -= $dataRK->stok_keluar;
        //         $dataBahanBaru->stok += $stokKeluarBaru;
        //         $dataRSBaru->stok += $stokKeluarBaru;
        //     }
        //     $dataRSLama->save();
        //     $dataBahanLama->save();
        //     $dataRSBaru->save();
        //     $dataBahanBaru->save();
        // }

        // $dataRK->id_bahan = $updateData['id_bahan'];
        $dataRK->stok_keluar = $updateData['stok_keluar'];

        if ($dataRK->save()) {
            return response([
                'message' => 'Update Riwayat Keluar Success',
                'data' => $dataRK
            ], 200);
        } //return data yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Riwayat Keluar Failed',
            'data' => $dataRK
        ], 400);  //return message saat produk gagat diedit
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $dataRK = RiwayatKeluar::find($id); //mencari data berdsaar id
        if (is_null($dataRK)) {
            return response([
                'message' => 'Riwayat Keluar Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        //ambil data dari tabel bahan
        $dataBahanLama = Bahan::where('id', $dataRK->id_bahan)->first();
        if (is_null($dataBahanLama)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        }
        //ambil data dari tabel remaining stock
        $dataRSLama = RemainingStock::where('tgl', $dataRK->tgl_keluar)
            ->where('id_bahan', $dataRK->id_bahan)
            ->first();
        if (is_null($dataRSLama)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        }

        $dataBahanLama->stok += $dataRK->stok_keluar;
        $dataRSLama->stok += $dataRK->stok_keluar;

        $dataRSLama->save();
        $dataBahanLama->save();

        if ($dataRK->delete()) {
            return response([
                'message' => 'Delete Riwayat Keluar Success',
                'data' => $dataRK
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Riwayat Keluar Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
