<?php

namespace App\Http\Controllers;

use App\Models\suppliers;

class suppliersController extends Controller
{
    public function index()
    {
        $suppliers = suppliers::all();

        return response()->json($suppliers);
    }
}
