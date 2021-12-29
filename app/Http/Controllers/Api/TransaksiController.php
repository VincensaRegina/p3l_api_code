<?php

namespace App\Http\Controllers\Api;

use App\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    //Display listing of resources
    public function index()
    {
        DB::statement(DB::raw('set @rownum = 0'));
        $data = DB::table('transaksi')
            ->join('reservasi', 'reservasi.id', '=', 'transaksi.id_reservasi')
            ->join('karyawan', 'karyawan.id', '=', 'transaksi.id_karyawan')
            ->leftJoin('kartu'd, 'kartu.id', '=', 'transaksi.id_kartu')
            ->join('customer', 'customer.id', '=', 'reservasi.id_customer')
            ->join('meja', 'meja.id', '=', 'reservasi.id_meja')
            ->select(
                DB::raw('@rownum := @rownum + 1 as no'),
                'transaksi.*',
                'karyawan.nama AS cashier',
                'kartu.*',
                'reservasi.id AS id_reservasi',
                'meja.no_meja',
                'customer.id AS id_customer',
                'customer.nama AS nama_customer',
            )
            ->where('transaksi.status', 'Lunas')
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
        $cek = Transaksi::where('id_reservasi',$storeData['id_reservasi']);
        if (!is_null($cek)) {
            return response([
                'message' => 'Transaksi sudah ada!',
                'data' => null
            ], 200);
        } //return message saat data tidak ditemukan
        $temp = -1;
        $validate = Validator::make($storeData, [
            'id_reservasi' => 'required|numeric',
            'id_karyawan' => 'required|numeric',
            'no_transaksi' => 'required|unique:transaksi,no_transaksi,' . $temp,
            'tgl_bayar' => 'required',
            'jam_bayar' => 'required|date_format:H:i:s',
            'tipe_bayar' => 'required',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
            'kode_edc' => '',
            'status' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Transaksi::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Transaksi Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    //Store a newly created resource in storage.
    public function storeMobile(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $cek = Transaksi::where('transaksi.id_reservasi', $storeData['id_reservasi'])
                ->first();
        if (!is_null($cek)) {
            return response([
                'message' => 'Transaksi sudah ada!',
                'data' => null
            ], 200);
        } //return message saat data tidak ditemukan
        $temp = -1;
        $validate = Validator::make($storeData, [
            'id_reservasi' => 'required|numeric',
            'no_transaksi' => 'required|unique:transaksi,no_transaksi,' . $temp,
            'subtotal' => 'required|numeric',
            'status' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Transaksi::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Transaksi Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    //Store a newly created resource in storage.
    public function updateTransaksi(Request $request, $id_reservasi)
    {
        $data = Transaksi::where('id_reservasi', $id_reservasi)->first();
        if (is_null($data)) {
            return response([
                'message' => 'Transaksi Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan
        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'id_karyawan' => 'required|numeric',
            'tgl_bayar' => 'required',
            'jam_bayar' => 'required|date_format:H:i:s',
            'tipe_bayar' => 'required',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
            'kode_edc' => '',
            'status' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data->id_karyawan = $updateData['id_karyawan'];
        $data->tgl_bayar = $updateData['tgl_bayar'];
        $data->jam_bayar = $updateData['jam_bayar'];
        $data->tipe_bayar = $updateData['tipe_bayar'];
        $data->subtotal = $updateData['subtotal'];
        $data->total = $updateData['total'];
        $data->id_kartu = $updateData['id_kartu'];
        $data->kode_edc = $updateData['kode_edc'];
        $data->status = $updateData['status'];

        if ($data->save()) {
            return response([
                'message' => 'Update Transaksi Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Transaksi Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Transaksi::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Transaksi Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Transaksi not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Hitung sudah berapa transaksi hari itu
    public function countTransaksi()
    {
        $data = Transaksi::whereDate('created_at', Carbon::today())->count();

        if ($data >= 0) {
            $data += 1;
            return response([
                'message' => 'Retrieve Transaksi Success',
                'data' => $data
            ], 200);
        }

        if (is_null($data)) {
            return response([
                'message' => 'Transaksi not found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }
    }
}
