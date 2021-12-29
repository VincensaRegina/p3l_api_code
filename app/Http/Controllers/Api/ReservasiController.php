<?php

namespace App\Http\Controllers\Api;

use App\Reservasi;
use App\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReservasiController extends Controller
{
    //Display listing of resources
    public function index()
    {
        //  $data = Reservasi::all(); //mengambil semua data product
        // DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('reservasi')
            ->join('customer', 'customer.id', '=', 'reservasi.id_customer')
            ->join('meja', 'reservasi.id_meja', '=', 'meja.id')
            ->select(
                'reservasi.*',
                'customer.id AS id_customer',
                'customer.nama',
                'customer.email',
                'customer.telp',
                'meja.id AS id_meja',
                'meja.no_meja',
            )
            ->orderBy('reservasi.tgl_reservasi')
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
            'id_customer' => 'required|numeric',
            'id_meja' => 'required|numeric',
            'tgl_reservasi' => 'required',
            'sesi' => 'required',
            'jenis' => 'required',
            'status' => 'required'
        ]); //membuat rule validasi input
        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Reservasi::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Reservasi Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Reservasi::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Reservasi Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Reservasi not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Data reservasi beserta pesanan untuk struk transaksi
    public function readReservasiOngoingMeja($no_meja)
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('reservasi')
            ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
            ->join('pesanan', 'pesanan.id_reservasi', '=', 'reservasi.id')
            ->join('menu', 'menu.id', '=', 'pesanan.id_menu')
            ->join('customer', 'customer.id', '=', 'reservasi.id_customer')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no_pesanan'),
                'reservasi.id',
                'reservasi.id_customer',
                'reservasi.id_meja',
                'reservasi.tgl_reservasi',
                'reservasi.sesi',
                'reservasi.jenis',
                'reservasi.status',
                'reservasi.waiter',
                'customer.nama AS nama_customer',
                'customer.email',
                'customer.telp',
                'meja.id AS id_meja',
                'meja.no_meja',
                'menu.nama AS nama_menu',
                'menu.harga',
                DB::raw('sum(pesanan.qty) as qty'),
                DB::raw('sum(pesanan.subtotal) as subtotal'),
                'pesanan.status AS status_pesanan',
            )
            ->groupBy(
                'reservasi.id',
                'reservasi.id_customer',
                'reservasi.id_meja',
                'reservasi.tgl_reservasi',
                'reservasi.sesi',
                'reservasi.jenis',
                'reservasi.status',
                'reservasi.waiter',
                'customer.nama',
                'customer.email',
                'customer.telp',
                'meja.id',
                'meja.no_meja',
                'menu.nama',
                'menu.harga',
                'pesanan.status'

            )
            ->where('reservasi.status', 'Ongoing')
            ->where('meja.no_meja', $no_meja)
            ->orderBy('no_pesanan')
            ->get(); //mencari data product berdasarkan id

        if (count($data) > 0) {
            return response([
                'message' => 'Retrieve Reservasi Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Reservasi not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

      // Data reservasi beserta pesanan untuk struk transaksi yg finished (klik icon)
      public function readReservasiFinished($idReservasi)
      {
          DB::statement(DB::raw('set @rownum = 0'));
          $data = DB::table('reservasi')
              ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
              ->join('pesanan', 'pesanan.id_reservasi', '=', 'reservasi.id')
              ->join('menu', 'menu.id', '=', 'pesanan.id_menu')
              ->join('customer', 'customer.id', '=', 'reservasi.id_customer')
              ->select(
                  DB::raw('@rownum := @rownum + 1 as no_pesanan'),
                  'reservasi.id',
                  'reservasi.id_customer',
                  'reservasi.id_meja',
                  'reservasi.tgl_reservasi',
                  'reservasi.sesi',
                  'reservasi.jenis',
                  'reservasi.status',
                  'reservasi.waiter',
                  'customer.nama AS nama_customer',
                  'customer.email',
                  'customer.telp',
                  'meja.id AS id_meja',
                  'meja.no_meja',
                  'menu.nama AS nama_menu',
                  'menu.harga',
                  DB::raw('sum(pesanan.qty) as qty'),
                  DB::raw('sum(pesanan.subtotal) as subtotal'),
                  'pesanan.status AS status_pesanan',
              )
              ->groupBy(
                  'reservasi.id',
                  'reservasi.id_customer',
                  'reservasi.id_meja',
                  'reservasi.tgl_reservasi',
                  'reservasi.sesi',
                  'reservasi.jenis',
                  'reservasi.status',
                  'reservasi.waiter',
                  'customer.nama',
                  'customer.email',
                  'customer.telp',
                  'meja.id',
                  'meja.no_meja',
                  'menu.nama',
                  'menu.harga',
                  'pesanan.status'
  
              )
              ->where('reservasi.id', $idReservasi)
              ->orderBy('no_pesanan')
              ->get(); //mencari data product berdasarkan id
  
          if (count($data) > 0) {
              return response([
                  'message' => 'Retrieve Reservasi Success',
                  'data' => $data
              ], 200);
          }
  
          return response([
              'message' => 'Reservasi not found',
              'data' => null
          ], 404); //return message data product tidak ditemukan
      }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $data = Reservasi::find($id); //mencari data product berdasar id

        if (is_null($data)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'id_customer' => 'required|numeric',
            'id_meja' => 'required|numeric',
            'tgl_reservasi' => 'required',
            'sesi' => 'required',
            'jenis' => 'required'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->id_customer = $updateData['id_customer'];
        $data->id_meja = $updateData['id_meja'];
        $data->tgl_reservasi = $updateData['tgl_reservasi'];
        $data->sesi = $updateData['sesi'];
        $data->jenis = $updateData['jenis'];

        if ($data->save()) {
            return response([
                'message' => 'Update Reservasi Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Reservasi Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Update status reservasi dan waiter yang print qr code
    public function updateStatus(Request $request, $id)
    {
        $data = Reservasi::find($id); //mencari data product berdasar id

        if (is_null($data)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'status' => 'required',
            'waiter' => 'required'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->status = $updateData['status'];
        $data->waiter = $updateData['waiter'];

        if ($data->save()) {
            return response([
                'message' => 'Update Status Reservasi Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Status Reservasi Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }


    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Reservasi::find($id); //mencari data berdsaar id

        if (is_null($data)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if ($data->delete()) {
            return response([
                'message' => 'Delete Reservasi Success',
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Reservasi Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
