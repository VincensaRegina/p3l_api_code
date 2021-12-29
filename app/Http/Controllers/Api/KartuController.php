<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Kartu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KartuController extends Controller
{
    //Display listing of resources
    public function index()
    {
        $data = Kartu::all(); //mengambil semua data product

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
        $temp = -1; //untuk key di validasi. kalo gaada, validasinya SQL integrity error.
        $validate = Validator::make($storeData, [
            'no_kartu' => 'required|digits:16|unique:kartu,no_kartu,' .$temp,
            'jenis_kartu' => 'required',
            'nama_kartu' => '',
            'tgl_kadaluarsa' => ''
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Kartu::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Kartu Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Kartu::find($id); //mencari data product berdasarkan id

        if(!is_null($data)) {
            return response([
                'message' => 'Retrieve Kartu Success',
                'data' => $data
            ],200);
        }

        return response([
            'message' => 'Kartu not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $data = Kartu::find($id); //mencari data product berdasar id
        if(is_null($data)) {
            return response([
                'message' => 'Kartu Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
           'no_kartu' => 'required|digits:16|unique:kartu,no_kartu,'.$data->id,
           'jenis_kartu' => 'required',
           'nama_kartu' => '',
           'tgl_kadaluarsa' => ''
        ]); //rule validasi input

        if($validate->fails()) 
            return response(['message' => $validate->errors()],400); //return error invalid input
        
        $data->no_kartu = $updateData['no_kartu'];
        $data->jenis_kartu = $updateData['jenis_kartu'];
        $data->nama_kartu = $updateData['nama_kartu'];
        $data->tgl_kadaluarsa = $updateData['tgl_kadaluarsa'];

        if($data->save()) {
            return response([
                'message' => 'Update Kartu Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Kartu Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

     //Get baris terakhir dalam tabel kartu
     public function getIdKartuLast()
     {
         $data = DB::table('kartu')->latest('id')->first(); //latest() will fetch only latest record according to created_at then first() will get only single record:
 
         if ($data != null) {
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

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Kartu::find($id); //mencari data berdsaar id

        if(is_null($data)) {
            return response([
                'message' => 'Kartu Not Found' ,
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if($data -> delete()) {
            return response([
                'message' => 'Delete Kartu Success' ,
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Kartu Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
