<?php

namespace App\Http\Controllers\Api;

use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    //Display listing of resources
    public function index()
    {
        $data = Customer::all(); //mengambil semua data product

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

    //Get baris terakhir dalam tabel customer
    public function getIdCustomerLast()
    {
        $data = DB::table('customer')->latest('id')->first(); //latest() will fetch only latest record according to created_at then first() will get only single record:

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
    //Store a newly created resource in storage.
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama' => 'required',
            'email' => 'email:rfc,dns|unique:customer,email, NULL',
            'telp' => 'regex:/[0][8][0-9]/|unique:customer,telp,NULL'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $data = Customer::create($storeData); //menambah data pada product baru
        return response([
            'message' => 'Add Customer Success',
            'data' => $data
        ], 200); //return message data product tidak ditemukan
    }

    // Display the specified resource.
    public function show($id)
    {
        $data = Customer::find($id); //mencari data product berdasarkan id

        if (!is_null($data)) {
            return response([
                'message' => 'Retrieve Customer Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Customer not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $data = Customer::find($id); //mencari data product berdasar id
        if (is_null($data)) {
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = Validator::make($updateData, [
            'nama' => 'required',
            'email' => 'email:rfc,dns|unique:customer,email,' . $data->id . ',id,deleted_at,NULL',
            'telp' => 'regex:/[0][8][0-9]/|unique:customer,telp,' . $data->id . ',id,deleted_at,NULL'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $data->nama = $updateData['nama'];
        $data->email = $updateData['email'];
        $data->telp = $updateData['telp'];

        if ($data->save()) {
            return response([
                'message' => 'Update Customer Success',
                'data' => $data
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Customer Failed',
            'data' => $data
        ], 400);  //return message saat produk gagat diedit
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $data = Customer::find($id); //mencari data berdsaar id

        if (is_null($data)) {
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        if ($data->delete()) {
            // $data['email'] = null;
            // $data['telp'] = null;
            // $data->save();
            return response([
                'message' => 'Delete Customer Success',
                'data' => $data
            ], 200); //return message data product berhasil dihapus
        }

        return response([
            'message' => 'Delete Customer Failed',
            'data' => null
        ], 400); //return message data product gagal dihapus
    }
}
