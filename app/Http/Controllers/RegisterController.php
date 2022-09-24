<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class RegisterController extends Controller
{
    public function register()
    {
        $validator = FacadesValidator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        // Jika register gagal
        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        // Simpan ke database
        User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make(request('password')),
        ]);

        // Kirimkan respon berhasil
        return response()->json(['messages' => 'Successfully register']);
    }
}
