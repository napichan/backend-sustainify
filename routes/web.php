<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AktivitasTransportasiController;

Route::get('/transportasi', [AktivitasTransportasiController::class, 'create']);
Route::post('/transportasi', [AktivitasTransportasiController::class, 'store']);

Route::get('/', function () {
    return view('welcome');
});
