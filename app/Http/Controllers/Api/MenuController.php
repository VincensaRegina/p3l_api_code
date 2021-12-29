<?php

namespace App\Http\Controllers\Api;

use App\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    //Display listing of resources
    public function index()
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = Menu::join('bahan', 'bahan.id', '=', 'menu.id_bahan')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no'),
                'menu.*',
                'bahan.id AS id_bahan',
                'bahan.nama_bahan',
                'bahan.unit AS unit_bahan',
                'bahan.stok AS stok_bahan'
            )
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
    //Display listing of resources
    public function getMenuBerdasarkanJenis($jenis)
    {
        $data = Menu::join('bahan', 'bahan.id', '=', 'menu.id_bahan')
            ->select(
                'menu.*',
                'bahan.id AS id_bahan',
                'bahan.nama_bahan',
                'bahan.unit AS unit_bahan',
                'bahan.stok AS stok_bahan'
            )
            ->where('menu.jenis', $jenis)
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
            'nama' => 'required',
            'desc' => 'required',
            'unit' => 'required',
            'jenis' => 'required',
            'serv_size' => 'required|numeric',
            'gambar' => 'required|image'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $file = $request->file('gambar');
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        $pathImage = $file->move(public_path("menu"), $filename);
        $storeData['gambar'] = $filename;
        // $storeData['gambar'] = "http://192.168.0.114:8000/" . $filename;
        // error_log($storeData['gambar']);

        $data = Menu::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Menu Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Menu::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Menu Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Menu not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $data = Menu::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'id_bahan' => 'required|numeric',
            'nama' => 'required',
            'desc' => 'required',
            'unit' => 'required',
            'jenis' => 'required',
            'serv_size' => 'required|numeric',
            'harga' => 'required|numeric',
            'gambar' => 'sometimes'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->id_bahan = $updateData['id_bahan'];
        $data->nama = $updateData['nama'];
        $data->desc = $updateData['desc'];
        $data->unit = $updateData['unit'];
        $data->jenis = $updateData['jenis'];
        $data->serv_size = $updateData['serv_size'];
        $data->harga = $updateData['harga'];

        if ($files = $request->file('gambar')) {
            $file = $request->file('gambar');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $pathImage = $file->move(public_path("menu"), $filename);
            $storeData['gambar'] = $filename;
            $data->gambar = $filename; //untuk sementara karena android gabisa pake yg 127.0.0
        }

        if ($data->save()) {
            return response([
                'message' => 'Update Menu Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Menu Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Menu::find($id); //mencari data berdsaar id

        if (is_null($data)) {
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if ($data->delete()) {
            return response([
                'message' => 'Delete Menu Success',
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Menu Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
