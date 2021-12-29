<?php

namespace App\Http\Controllers\Api;

use App\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    //Display listing of resources
    public function indexRiwayat()
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('pesanan')
            ->join('menu', 'menu.id', '=', 'pesanan.id_menu')
            ->join('reservasi', 'reservasi.id', '=', 'pesanan.id_reservasi')
            ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no'),
                'pesanan.*',
                'reservasi.id AS id_reservasi',
                DB::raw('meja.no_meja AS "Nomor Meja"'), 
                'menu.nama AS nama_menu',
            )
            ->where('reservasi.status', '=', 'Finished')
            ->where('pesanan.locked','=','yes')
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
     public function indexOngoing()
     {
         DB::statement(DB::raw('set @rownum = 0'));
         $data = Pesanan::join('menu', 'menu.id', '=', 'pesanan.id_menu')
             ->join('reservasi', 'reservasi.id', '=', 'pesanan.id_reservasi')
             ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
             ->join('transaksi', 'transaksi.id_reservasi', '=', 'reservasi.id')
             ->select(
                 DB::raw('@rownum := @rownum + 1 as no'),
                 'pesanan.*',
                 'reservasi.id AS id_reservasi',
                 DB::raw('meja.no_meja AS "Nomor Meja"'), 
                 'menu.nama AS nama_menu',
             )
             ->where('reservasi.status', '=', 'Ongoing')
             ->where('pesanan.locked','=','yes')
             ->where('transaksi.status','=','Belum')
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
    public function indexAll()
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = Pesanan::join('menu', 'menu.id', '=', 'pesanan.id_menu')
            ->join('reservasi', 'reservasi.id', '=', 'pesanan.id_reservasi')
            ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no'),
                'pesanan.*',
                'reservasi.id AS id_reservasi',
                DB::raw('meja.no_meja AS "Nomor Meja"'), 
                'menu.nama AS nama_menu',
            )
            ->where('pesanan.locked','=','yes')
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

    //Pesanan untuk customer tertentu
    public function indexSpecificCustomer($id_reservasi)
    {
        $data = Pesanan::join('menu', 'menu.id', '=', 'pesanan.id_menu')
            ->join('reservasi', 'reservasi.id', '=', 'pesanan.id_reservasi')
            ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
            ->join('bahan', 'bahan.id', '=', 'menu.id_bahan')
            ->select(
                'pesanan.*',
                'menu.nama AS nama_menu',
                'menu.harga AS harga_menu',
                'menu.serv_size',
                'menu.unit AS unit_menu',
                'menu.gambar AS gambar',
                'bahan.id AS id_bahan',
                'bahan.stok AS stok_bahan'
            )
            ->where('reservasi.status', 'Ongoing')
            ->where('pesanan.locked','no')
            ->where('reservasi.id', $id_reservasi)
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
        ], 200); //return message data product kosong
    }

    //Store a newly created resource in storage.
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_reservasi' => 'required|numeric',
            'id_menu' => 'required|numeric',
            'qty' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'locked' => 'required',
            'status' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Pesanan::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Pesanan Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Pesanan::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Pesanan Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Pesanan not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

     // Cek menu sudah masuk cart atau belum.
     public function cekMenudiCart($id_reservasi, $id_menu)
     {
        $data = Pesanan::where('pesanan.id_reservasi', $id_reservasi)
                ->where('pesanan.id_menu', $id_menu)
                ->where('pesanan.locked', 'no')
                ->get();
         if (count($data) > 0) {
             return response([
                 'message' => 'Ada data',
                 'data' => $data
             ], 200);
         }
 
         return response([
             'message' => 'Tidak ada data',
             'data' => null
         ], 200); //return message data product tidak ditemukan
     }

     //Update qty
     public function updateQty(Request $request, $id)
     {
         $data = Pesanan::find($id); //mencari data product berdasar id
         if (is_null($data)) {
             return response([
                 'message' => 'Pesanan Not Found',
                 'data' => null
             ], 404);
         } //return message saat data tidak ditemukan
 
         $updateData = $request->all(); //abil semua input dari api client
         $validate = Validator::make($updateData, [
             'qty' => 'required',
             'subtotal' => 'required'
         ]); //rule validasi input
 
         if ($validate->fails())
             return response(['message' => $validate->errors()], 400); //return error invalid input
 
         $data->qty = $updateData['qty'];
         $data->subtotal = $updateData['subtotal'];
 
 
         if ($data->save()) {
             return response([
                 'message' => 'Update Status Pesanan Success',
                 'data' => $data
             ], 200);
         } //return data yang telah diedit dalam bentuk json
 
 
         return response([
             'message' => 'Update Status Pesanan Failed',
             'data' => $data
         ], 400);  //return message saat produk gagat diedit
     }

    
    // Update status the specified resource in storage.
    public function updateStatus(Request $request, $id)
    {
        $data = Pesanan::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Pesanan Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'status' => 'required'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->status = $updateData['status'];

        if ($data->save()) {
            return response([
                'message' => 'Update Status Pesanan Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Status Pesanan Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

       // Update status the specified resource in storage.
       public function updateLocked(Request $request, $id)
       {
           $data = Pesanan::find($id); //mencari data product berdasar id
           if (is_null($data)) {
               return response([
                   'message' => 'Pesanan Not Found',
                   'data' => null
               ], 404);
           } //return message saat data tidak ditemukan
   
           $updateData = $request->all(); //abil semua input dari api client
           $validate = Validator::make($updateData, [
               'locked' => 'required'
           ]); //rule validasi input
   
           if ($validate->fails())
               return response(['message' => $validate->errors()], 400); //return error invalid input
   
           $data->locked = $updateData['locked'];
   
           if ($data->save()) {
               return response([
                   'message' => 'Update Status Pesanan Success',
                   'data' => $data
               ], 200);
           } //return data yang telah diedit dalam bentuk json
   
   
           return response([
               'message' => 'Update Status Pesanan Failed',
               'data' => $data
           ], 400);  //return message saat produk gagat diedit
       }
   
    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Pesanan::find($id); //mencari data berdsaar id

        if (is_null($data)) {
            return response([
                'message' => 'Pesanan Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if ($data->delete()) {
            return response([
                'message' => 'Delete Pesanan Success',
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Pesanan Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
