<?php

namespace App\Http\Controllers;


use App\Models\orders;
use Illuminate\Http\Request;

class ordersController extends Controller
{
    public function index()
    {
        $orders = orders::all();

        return response()->json($orders);
    }

    //recoge los pedidos de un usuario concreto por su usuario
    public function getOrders($username)
    {
        $orders = orders::where('username', $username)->get();

        return response()->json($orders);
    }

    //añade un pedido a un usuario concreto por su usuario
    public function addOrder(Request $request, $username)
    {
        $order = new orders;
        $order->username = $username;
        
        $order->save();

        return response()->json([
            'message' => 'Order added successfully'
        ], 200);
    }
}
