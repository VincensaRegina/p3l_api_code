<?php

namespace App\Http\Controllers\Api;

use App\RiwayatMasuk;
use App\Bahan;
use App\RemainingStock;
use App\RiwayatKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RiwayatMasukController extends Controller
{
    //Display listing of resources
    public function index()
    {
        $data = DB::table('riwayat_masuk')
            ->join('bahan', 'bahan.id', '=', 'riwayat_masuk.id_bahan')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no'),
                'riwayat_masuk.*',
                'bahan.nama_bahan'
            )
            ->orderByDesc('riwayat_masuk.tgl_masuk')
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
            'tgl_masuk' => 'required',
            'stok_masuk' => 'required|numeric',
            'biaya' => 'required|numeric'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        //  //cek apakah sudah ada baris bahan masuk dgn tgl dan id bahan yg sama
        // $cek = RiwayatMasuk::where('tgl_masuk', $storeData['tgl_masuk'])
        //     ->where('id_bahan', $storeData['id_bahan'])
        //     ->first();

        // if (!is_null($cek)) {
        //     $cek->stok_masuk += $storeData['stok_masuk'];
        //     $cek->biaya += $storeData['biaya'];
        //     if ($cek->save()) {
        //         return response([
        //             'message' => 'Update Riwayat Masuk Success',
        //             'data' => $cek
        //         ], 200);
        //     } //return data yang telah diedit dalam bentuk json
        // }

        $data = RiwayatMasuk::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Riwayat Masuk Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = RiwayatMasuk::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Riwayat Masuk Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Riwayat Masuk not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $dataRM = RiwayatMasuk::find($id); //mencari data product berdasar id
        if (is_null($dataRM)) {
            return response([
                'message' => 'Riwayat Masuk Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok_masuk' => 'required|numeric',
            'biaya' => 'required|numeric'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        //Ambil data dari request dan validasi
        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok_masuk' => 'required|numeric',
        ]); //rule validasi input
        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        //Dimasukin ke variabel biar gampang aja
        $stokMasukBaru = $updateData['stok_masuk'];
        $biayaBaru = $updateData['biaya'];

        //ambil data dari tabel bahan
        $dataBahanLama = Bahan::where('id', $dataRM->id_bahan)->first();
        if (is_null($dataBahanLama)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        }
        //ambil data dari tabel remaining stock
        $dataRSLama = RemainingStock::where('tgl', $dataRM->tgl_masuk)
            ->where('id_bahan', $dataRM->id_bahan)
            ->first();
            
        if (is_null($dataRSLama)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        }

        if ($stokMasukBaru > $dataRM->stok_masuk) { //jika yg masuk nambah
            $selisih = $stokMasukBaru - $dataRM->stok_masuk;
            $dataBahanLama->stok += $selisih;
            $dataRSLama->stok += $selisih;
        } else if ($stokMasukBaru < $dataRM->stok_masuk) { //jika yg masuk berkurang
            $selisih = $dataRM->stok_masuk - $stokMasukBaru;
            if ($selisih > $dataBahanLama->stok) {
                return response([
                    'message' => 'Stok bahan tidak cukup!',
                    'data' => null
                ], 400);
            } else {
                $dataBahanLama->stok -= $selisih;
                $dataRSLama->stok -= $selisih;
            }
        }
        $dataRSLama->save();
        $dataBahanLama->save();
        $dataRM->stok_masuk = $stokMasukBaru;
        $dataRM->biaya = $biayaBaru;

        if ($dataRM->save()) {
            return response([
                'message' => 'Update Riwayat Masuk Success',
                'data' => $dataRM
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Riwayat Masuk Failed',
            'data' => $dataRM
        ], 400);  //return message saat produk gagat diedit
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $dataRM = RiwayatMasuk::find($id); //mencari data berdsaar id
        if (is_null($dataRM)) {
            return response([
                'message' => 'Riwayat Masuk Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }
        //jika dataRK sudah ada utk bahan dan tgl tertentu, maka tdk bisa didelete
        $dataRK = RiwayatKeluar::where('id_bahan', $dataRM->id_bahan)
            ->where('tgl_keluar', $dataRM->tgl_masuk)
            ->first();
        if (!is_null($dataRK)) {
            return response([
                'message' => 'Delete riwayat masuk tidak diperbolehkan karena bahan sudah digunakan!',
                'data' => null
            ], 400); //return message data product tidak ditemukan
        }

        //ambil data dari tabel bahan
        $dataBahanLama = Bahan::where('id', $dataRM->id_bahan)->first();
        if (is_null($dataBahanLama)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        }
        //ambil data dari tabel remaining stock
        $dataRSLama = RemainingStock::where('tgl', $dataRM->tgl_masuk)
            ->where('id_bahan', $dataRM->id_bahan)
            ->first();
        if (is_null($dataRSLama)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        }
        //riwayat masuk yg dihapus, data rs dan bahannya dikembalikan
        $dataBahanLama->stok -= $dataRM->stok_masuk;
        $dataRSLama->stok -= $dataRM->stok_masuk;

        $dataRSLama->save();
        $dataBahanLama->save();


        if ($dataRM->delete()) {
            return response([
                'message' => 'Delete Riwayat Masuk Success',
                'data' => $dataRM
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Riwayat Masuk Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
