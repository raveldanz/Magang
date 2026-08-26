<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // API Login untuk Mendapatkan Token & Info Role
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kredensial email atau password salah.'
            ], 401);
        }

        // Hapus token lama jika ada, lalu buat token baru
        $user->tokens()->delete();
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role, // Info Role dikirimkan di API
                ],
                'token' => $token
            ]
        ], 200);
    }

    // API Cek User Profile (Membutuhkan Bearer Token)
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('studentProfile')
        ]);
    }

    // API Logout (Revoke Token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }
}