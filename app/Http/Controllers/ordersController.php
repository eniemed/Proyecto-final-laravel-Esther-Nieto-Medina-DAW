<?php

namespace App\Http\Controllers;

use App\Models\orders;

class ordersController extends Controller
{
    public function index()
    {
        $orders = orders::all();

        return response()->json($orders);
    }
}
