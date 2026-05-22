<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AktivitasTransportasiController;
use App\Http\Controllers\AktivitasRumahTanggaController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\RumahTanggaController; // ← tambahan

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('aktivitas', AktivitasTransportasiController::class);

    Route::get('/rumah-tangga', [AktivitasRumahTanggaController::class, 'index']);
    Route::post('/rumah-tangga', [AktivitasRumahTanggaController::class, 'store']);
    Route::put('/rumah-tangga/{id}', [AktivitasRumahTanggaController::class, 'update']);
    Route::delete('/rumah-tangga/{id}', [AktivitasRumahTanggaController::class, 'destroy']);
});

Route::get('/kendaraan', [KendaraanController::class, 'index']);
Route::get('/rumah-tangga-list', [RumahTanggaController::class, 'index']); // ← tambahan

Route::post('/login', function (Request $request) {
    try {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan server',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/register', function (Request $request) {
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil',
            'token' => $token,
            'user' => $user
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan server',
            'error' => $e->getMessage()
        ], 500);
    }
});