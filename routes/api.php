<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AktivitasTransportasiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('aktivitas', AktivitasTransportasiController::class);
});

Route::post('/login', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Login gagal'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'user' => $user,
        'token' => $token
    ]);
});