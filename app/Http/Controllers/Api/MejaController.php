<?php

namespace App\Http\Controllers\Api;

use App\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MejaController extends Controller
{
    //Display listing of resources
    public function index()
    {
        $data = Meja::orderBy('no_meja', 'asc')->get(); //mengambil semua data product

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

    //Get nomor meja yang kosong saat sesi dan tanggal tertentu
    public function getMejaKosong($tgl_reservasi, $sesi)
    {
        $data = Meja::whereNotIn(
                'meja.no_meja',
                fn ($query) =>
                $query->select('meja.no_meja')
                    ->from('meja')
                    ->join('reservasi', 'reservasi.id_meja', '=', 'meja.id')
                    ->where('reservasi.tgl_reservasi', '=', $tgl_reservasi)
                    ->where('reservasi.sesi', '=', $sesi)
            )->orderBy('meja.no_meja', 'asc')
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


    //Get nomor meja yang kosong saat sesi dan tanggal tertentu
    public function getIdMeja($noMeja)
    {
        $data = DB::table('meja')
            ->select('meja.id')
            ->where('meja.no_meja', '=', $noMeja)
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
            'no_meja' => 'required',
            'status' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        return $this->cekNoMeja('save', $storeData, null);
    }

    public function cekNoMeja($jenis, $inputForm, $dataTabel)
    {
        if ($jenis == "save") {
            //cek apakah no meja sudah ada di tabel (karena ga pake unique)
            $cek = Meja::where('no_meja', $inputForm['no_meja'])->get();

            if (count($cek) > 0) {
                return response([
                    'message' => 'Nomor meja sudah digunakan!'
                ], 400);
            }
        }

        //cek apakah no meja yang sudah dihapus (soft delete) sudah ada di tabel (karena ga pake unique)
        $cekTrashed = Meja::onlyTrashed()
            ->where('no_meja', $inputForm['no_meja'])
            ->first();

        if (!is_null($cekTrashed)) {
            $cekTrashed->restore(); //untuk membuat deleted_at menjadi null
            if ($jenis == "update") $dataTabel->delete(); //yg diupdate didelete karena yg cekTrashed sudah direstore.
            return response([
                'message' => 'Add Meja Success',
                'data' => $cekTrashed
            ], 200); //return message data product tidak ditemukan
        }

        if ($jenis == "save") { //untuk save
            $data = Meja::create($inputForm);
            return response([
                'message' => 'Add Meja Success',
                'data' => $data
            ], 200); //return message data product tidak ditemukan
        } else if ($jenis == "update") { //untuk update

            $dataTabel->no_meja = $inputForm['no_meja'];
            $dataTabel->status = $inputForm['status'];

            if ($dataTabel->save()) {
                return response([
                    'message' => 'Update Meja Success',
                    'data' => $dataTabel
                ], 200);
            } //return data yang telah diedit dalam bentuk json

            return response([
                'message' => 'Update Meja Failed',
                'data' => $dataTabel
            ], 400);  //return message saat produk gagat diedit
        }
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Meja::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Meja Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Meja not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

      // Display the specified resource.
      public function readMejaTransaksi()
      {
          $data = Meja::join('reservasi','reservasi.id_meja','=','meja.id')
                ->join('transaksi','transaksi.id_reservasi','=','reservasi.id')
                ->where('transaksi.status','Belum')
                ->orderBy('no_meja')
                ->get(); //mencari data product berdasarkan id
  
          if (count($data) > 0) {
              return response([
                  'message' => 'Retrieve Meja Success',
                  'data' => $data
              ], 200);
          }
  
          return response([
              'message' => 'Meja not found',
              'data' => null
          ], 404); //return message data product tidak ditemukan
      }



    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $data = Meja::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Meja Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'no_meja' => 'required|unique:meja,no_meja,' . $data->id . ',id,deleted_at,NULL', //'unique:table,email_column_to_check,id_to_ignore, asal tabel, where(deleted_at, null)'
            'status' => 'required',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        return $this->cekNoMeja('update', $updateData, $data);
    }

    // Update the specified resource in storage.
    public function updateMejaStatus(Request $request, $id)
    {
        $data = Meja::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Meja Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'status' => 'required',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->status = $updateData['status'];

        if ($data->save()) {
            return response([
                'message' => 'Update Meja Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Meja Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Meja::find($id); //mencari data berdsaar id

        if (is_null($data)) {
            return response([
                'message' => 'Meja Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if ($data->delete()) {
            $data['no_meja'] = null;
            return response([
                'message' => 'Delete Meja Success',
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Meja Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
