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

    public function getOrders($username)
    {
        $orders = orders::where('username', $username)->get();

        return response()->json($orders);
    }
}
