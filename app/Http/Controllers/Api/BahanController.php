<?php

namespace App\Http\Controllers\Api;

use App\Bahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BahanController extends Controller
{
    //Display listing of resources
    public function index()
    {
        $data = Bahan::all(); //mengambil semua data product

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
            'nama_bahan' => 'required',
            'stok' => 'required|numeric',
            'unit' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Bahan::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Bahan Success',
            'data' => $data
        ], 201);
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Bahan::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Bahan Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Bahan not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }


    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {

        $data = Bahan::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'nama_bahan' => '',
            'unit' => '',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->nama_bahan = $updateData['nama_bahan'];
        $data->unit = $updateData['unit'];

        if ($data->save()) {
            return response([
                'message' => 'Update Bahan Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Bahan Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }x

    // Update the specified resource in storage.
    public function tambahStok(Request $request, $id)
    {
        $data = Bahan::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok' => 'numeric',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->stok = $updateData['stok'] + $data->stok;

        if ($data->save()) {
            return response([
                'message' => 'Tambah Stok Success!',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Tambah Bahan Gagal!',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Update the specified resource in storage.
    public function buangStok(Request $request, $id)
    {
        $data = Bahan::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok' => 'numeric',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        if ($data->stok >= $updateData['stok'])
            $data->stok = $data->stok - $updateData['stok'];
        else {
            return response([
                'message' => 'Stok bahan tidak mencukupi!',
                'data' => null
            ], 400);  //return message saat produk gagat diedit
        }

        if ($data->save()) {
            return response([
                'message' => 'Buang Bahan Success!',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Buang Bahan Gagal!',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Bahan::find($id); //mencari data berdsaar id

        if (is_null($data)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if ($data->delete()) {
            return response([
                'message' => 'Delete Bahan Success',
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Bahan Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }

    ///// MOBILE
    // Cek bahan masih ada apa engga.
    public function updateStokMobile(Request $request, $id, $jenisUbah)
    {
        $servXqty = $request->all(); //ambil data kuantitas
        $servXqty = $servXqty['servXqty'];

        $data = Bahan::join('menu', 'menu.id_bahan', '=', 'bahan.id')
            ->select(
                'bahan.*',
                'menu.serv_size',
            )
            ->where('bahan.id', $id)
            ->first(); // Cari data bahan berdasarkan id

        if (is_null($data)) {
            return response([
                'message' => 'Bahan not Found',
                'data' => null
            ], 404);
        } //return not found
        if ($jenisUbah == "tambahP") { //tambah pesanan, maka stok berkurang
            if ($data->stok >= $servXqty) { //jika mencukupi
                $data->stok = $data->stok - $servXqty;
                if ($data->save()) {
                    return response([
                        'message' => 'Update Bahan Success',
                        'data' => $data
                    ], 200);
                } //return data yang telah diedit dalam bentuk json
            } else { //jika tidak mencukupi
                return response([
                    'message' => 'Maaf, stok bahan tidak mencukupi!',
                    'data' => $data
                ], 406);
            }
        } else if ($jenisUbah == "hapusP") { //hapus pesanan, maka stok bertambah
            $data->stok += $servXqty;
            if ($data->save()) {
                return response([
                    'message' => 'Update Bahan Success',
                    'data' => $data
                ], 200);
            } //return data yang telah diedit dalam bentuk json
        } else if ($jenisUbah == "editLebih") { //qty awal > qty baru (pesanan berkurang, stok kembali)
            $data->stok += $servXqty;
            if ($data->save()) {
                return response([
                    'message' => 'Update Remaining Stock Success',
                    'data' => $data
                ], 200);
            } //return data yang telah diedit dalam bentuk json
        } else if ($jenisUbah == "editKurang") { //qty awal > qty baru (pesanan berkurang, stok kembali)
            $data->stok -= $servXqty;
            if ($data->save()) {
                return response([
                    'message' => 'Update Remaining Stock Success',
                    'data' => $data
                ], 200);
            } //return dat
        }
    }
}
