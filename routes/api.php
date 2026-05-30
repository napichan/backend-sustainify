<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AktivitasTransportasiController;
use App\Http\Controllers\AktivitasRumahTanggaController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\RumahTanggaController;
use App\Http\Controllers\AdminController;

// Admin routes
Route::get('/admin/users', [AdminController::class, 'getUsers']);
Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']);
Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
Route::get('/admin/aktivitas', [AdminController::class, 'getAllAktivitas']);
Route::get('/admin/stats', [AdminController::class, 'getStats']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('aktivitas', AktivitasTransportasiController::class);

    Route::get('/rumah-tangga', [AktivitasRumahTanggaController::class, 'index']);
    Route::post('/rumah-tangga', [AktivitasRumahTanggaController::class, 'store']);
    Route::put('/rumah-tangga/{id}', [AktivitasRumahTanggaController::class, 'update']);
    Route::delete('/rumah-tangga/{id}', [AktivitasRumahTanggaController::class, 'destroy']);

    // Update profile
    Route::put('/profile', function (Request $request) {
        $user = $request->user();

        if ($request->name) {
            $user->name = $request->name;
        }

        if ($request->new_password) {
            if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->getAttributes()['password'])) {
                return response()->json(['success' => false, 'message' => 'Password lama salah'], 422);
            }
            $user->password = $request->new_password;
        }

        $user->save();

        return response()->json(['success' => true, 'user' => $user]);
    });
});

Route::get('/kendaraan', [KendaraanController::class, 'index']);
Route::get('/rumah-tangga-list', [RumahTanggaController::class, 'index']);

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
            'password' => $validated['password'], // cast 'hashed' di User.php yang hash
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