<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Karyawan;
use Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $registrationData = $request->all();
        $temp = -1; //untuk key di validasi. kalo gaada, validasinya SQL integrity error.
        $validate = Validator::make($registrationData, [
            'nama' => 'required|max:100',
            'email' => 'required|email:rfc,dns|unique:karyawan,email,' . $temp, //di unique ada id supaya id baris tersebut tidak terhitung
            'telp' => 'required|regex:/[0][8][0-9]/|unique:karyawan,telp,' . $temp,
            'jk' => 'required',
            'posisi' => 'required',
            'tgl_gabung' => 'required',
            'status' => 'required',
            'password' => 'required'
        ]);

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $registrationData['password'] = bcrypt($request->password); //enkripsi password
        $user = Karyawan::create($registrationData); //membuat user baru
        // $user->sendApiEmailVerificationNotification();
        return response([
            'message' => 'Register Success',
            'user' => $user,
        ], 200); //return data user dalam bentuk json
    }

    public function login(Request $request)
    {

        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password' => 'required'
        ]); //membuat rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input    


        if (!Auth::attempt($loginData))
            return response(['message' => 'Invalid Credentials'], 401); //return error gagal login    

        // Get the currently authenticated user...
        $user = Auth::user();

        if ($user['status'] == "Non-Aktif") {
            return response(['message' => 'Akun non-aktif!'], 401); //return error gagal login  
        }

        $token = $user->createToken('Authentication Token')->accessToken; //generate token

        return response([
            'message' => 'Authenticated',
            'user' => $user,
            'token_type' => 'Bearer',
            'access_token' => $token
        ]); //return data user dan token dalam bentuk json

    }

    // public function details()
    // {
    //     $user = Auth::user();
    //     return response()->json(['success' => $user], $this->successStatus);
    // }

    public function update(Request $request, $id)
    {
        $user = Karyawan::find($id); //mencari data karyawan berdasar id
        if (is_null($user)) {
            return response([
                'message' => 'User Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateUser = $request->all(); //ambil semua input dari api client
        $validate = Validator::make($updateUser, [
            'nama' => 'required|max:100',
            'email' => 'required|email:rfc,dns|unique:karyawan,email,' . $user->id,
            'telp' => 'required|regex:/[0][8][0-9]/|unique:karyawan,telp,' . $user->id,
            'posisi' => 'required',
            'jk' => 'required',
            'tgl_gabung' => ''
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $user->nama = $updateUser['nama']; //edit nama
        $user->email = $updateUser['email']; //edit nama
        $user->telp = $updateUser['telp']; //edit telp
        $user->posisi = $updateUser['posisi']; //edit posisi
        $user->jk = $updateUser['jk']; //edit posisi
        $user->tgl_gabung = $updateUser['tgl_gabung'];
        // $event->photo = $updateData['photo'];

        // if ($files = $request->file('photo')) {
        //     $file = $request->file('photo');
        //     $extension = $file->getClientOriginalExtension();
        //     $filename = 'users/' . time() . '.' . $extension;
        //     $pathImage = $file->move(public_path("users"), $filename);
        //     $user->photo = url($filename);
        // }

        if ($user->save()) {
            return response([
                'message' => 'Update User Success',
                'data' => $user
            ], 200);
        } //return data yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update User Failed',
            'data' => $user
        ], 400);  //return message saat produk gagat diedit
    }


    public function getAllUsers()
    {
        $user = Karyawan::where('id','>','0')
                ->get(); //mencari data product berdasar id
        if (count($user) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $user
            ], 200);
        } //return data semua product dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data product kosong
    }

    //utk menghapus 1 data product (delete) 
    public function nonAktif(Request $request, $id)
    {
        $user = Karyawan::find($id); //mencari data berdsaar id

        if (is_null($user)) {
            return response([
                'message' => 'User Not Found',
                'data' => null
            ], 404); //return message data product tidak ditemukan
        }

        $updateUser = $request->all(); //ambil semua input dari api client
        $validate = VAlidator::make($updateUser, [
            'status' => 'required'
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $user->status = $updateUser['status'];

        if ($user->save()) {
            return response([
                'message' => 'Update Status Karyawan Success',
                'data' => $user
            ], 200);
        } //return data yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Status Karyawan Failed',
            'data' => $user
        ], 400);  //return message saat produk gagat diedit
    }

    //utk menghapus 1 data product (delete) 
    public function show(Request $request, $id)
    {
        $user = Karyawan::find($id); //mencari data berdsaar id


        if (!is_null($user)) {
            return response([
                'message' => 'Retrieve Karyawan Success',
                'data' => $user
            ], 200);
        }

        return response([
            'message' => 'Karyawan not found',
            'data' => null
        ], 404); //return message data product tidak ditemukan
    } //return data yang telah diedit dalam bentuk json

    public function hashCheck(Request $request, $id)
    {
        $user = Karyawan::find($id); //mencari data product berdasarkan id
        $userData = $request->all(); //abil semua input dari api client
        if (!Hash::check($userData['password'], $user['password'])) {
            return response([
                'message' => 'Password saat ini salah!',
                'data' => null
            ], 400); //return message data product tidak ditemukan
        }
    }

    public function changePassword(Request $request, $id)
    {
        $user = Karyawan::find($id); //mencari data product berdasar id
        if (is_null($user)) {
            return response([
                'message' => 'Karyawan Not Found',
                'data' => null
            ], 404);
        } //return message saat data tidak ditemukan

        $updateData = $request->all(); //abil semua input dari api client
        $validate = VAlidator::make($updateData, [
            'password' => 'required',
        ]); //rule validasi input

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400); //return error invalid input

        $user['password'] = bcrypt($request->password);

        if ($user->save()) {
            return response([
                'message' => 'Update Password Sukses',
                'data' => $user
            ], 200);
        } //return data yang telah diedit dalam bentuk json


        return response([
            'message' => 'Update Password Failed',
            'data' => $user
        ], 400);  //return message saat produk gagat diedit
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}
