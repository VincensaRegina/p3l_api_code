<?php

namespace App\Http\Controllers\Api;

use App\RemainingStock;
use App\Bahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RemainingStockController extends Controller
{
    //Display listing of resources
    public function index()
    {
        $data = RemainingStock::all(); //mengambil semua data product

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
            'tgl' => 'required',
            'id_bahan' => 'required',
            'stok' => 'required|numeric',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = RemainingStock::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Remaining Stock Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    //Get id remaining stok dari tgl dan id bahan
    public function getIdRemainingStock($tgl, $idBahan)
    {
        $data = DB::table('remaining_stock')
            ->select('remaining_stock.id')
            ->where('remaining_stock.tgl', '=', $tgl)
            ->where('remaining_stock.id_bahan', '=', $idBahan)
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

    // Display the specified resource.
    public function show($id)
    {
        $data = RemainingStock::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Remaining Stock Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Remaining Stock not found',
            'data' => $data
        ], 404); //return message data product tidak ditemukan
    }

    // Update the specified resource in storage.
    public function updateTambahStok(Request $request, $id)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln("Hello from Terminal");
        $data = RemainingStock::find($id);
        $out->writeln($data);


        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok' => 'required|numeric',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input
        // $data = RemainingStock::where('tgl', '=', $tgl)
        //     ->where('id_bahan', '=', $idBahan)
        //     ->first();

        $data->stok = $updateData['stok'] + $data->stok;

        if ($data->save()) {
            return response([
                'message' => 'Update Remaining Stock Success',
                'data' => $data
            ], 200);
        }
        //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Remaining Stock Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit

    }

    // Update the specified resource in storage.
    public function updateBuangStok(Request $request, $id)
    {
        $data = RemainingStock::find($id);

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'stok' => 'required|numeric',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input
        // $data = RemainingStock::where('tgl', '=', $tgl)
        //     ->where('id_bahan', '=', $idBahan)
        //     ->first();

        if ($updateData['stok'] > $data->stok) {
            return response([
                'message' => 'Stok tidak mencukupi!',
                'data' => null
            ], 400);
        }

        $data->stok = $data->stok - $updateData['stok'];

        if ($data->save()) {
            return response([
                'message' => 'Update Remaining Stock Success',
                'data' => $data
            ], 200);
        }
        //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Remaining Stock Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    //////MOBILE
    // Update keluar stok bahan
    public function updateRSMobile(Request $request, $id_bahan, $jenisUbah)
    {
        $request = $request->all(); //abil semua input dari api client
        $servXqty = $request['servXqty'];

        $currentDate = Carbon::now()->format('Y-m-d'); //dapat date hari ini 

        $dataRS = RemainingStock::where('id_bahan', $id_bahan) //cari data remaining stock dgn tgl dan id bahan tertentu
            ->where('tgl', $currentDate)->first();

        if (is_null($dataRS)) {
            return response([
                'message' => 'Maaf, stok bahan habis.',
                'data' => null
            ], 404);
        } //return not found

        if ($jenisUbah == "tambahP") {
            if ($dataRS->stok >= $servXqty) { //jika mencukupi
                $dataRS->stok = $dataRS->stok - $servXqty;
                if ($dataRS->save()) {
                    return response([
                        'message' => 'Update Remaining Stock Success',
                        'data' => $dataRS
                    ], 200);
                } //return data yang telah diedit dalam bentuk json
            } else { //jika tidak mencukupi
                return response([
                    'message' => 'Maaf, stok tidak mencukupi!',
                    'data' => $dataRS
                ], 406);
            }
        } else if ($jenisUbah == "hapusP") { //pesanan dihapus
            $dataRS->stok += $servXqty;
            if ($dataRS->save()) {
                return response([
                    'message' => 'Update Remaining Stock Success',
                    'data' => $dataRS
                ], 200);
            } //return data yang telah diedit dalam bentuk json
        } else if($jenisUbah == "editLebih") { //qty awal > qty baru (pesanan berkurang, stok kembali)
            $dataRS->stok += $servXqty;
            if ($dataRS->save()) {
                return response([
                    'message' => 'Update Remaining Stock Success',
                    'data' => $dataRS
                ], 200);
            } //return data yang telah diedit dalam bentuk json
        }  else if($jenisUbah == "editKurang") { //qty awal > qty baru (pesanan berkurang, stok kembali)
            if ($dataRS->stok >= $servXqty) { //jika mencukupi
                $dataRS->stok -= $servXqty;
                if ($dataRS->save()) {
                    return response([
                        'message' => 'Update Remaining Stock Success',
                        'data' => $dataRS
                    ], 200);
                } //return data yang telah diedit dalam bentuk json
            } else { //jika tidak mencukupi
                return response([
                    'message' => 'Maaf, stok tidak mencukupi!',
                    'data' => $dataRS
                ], 406);
            }
        }

        return response([
            'message' => 'Update Remaining Stock Failed',
            'data' => null
        ], 400);  //return message saat produk gagat diedit
    }
}
