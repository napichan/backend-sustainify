<?php

namespace App\Http\Controllers;

use App\Models\RumahTangga;
use Illuminate\Http\Request;

class RumahTanggaController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => RumahTangga::all()
        ]);
    }
}