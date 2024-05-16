<?php

namespace App\Http\Controllers;

use App\Models\flavors;
use Illuminate\Http\Request;
use App\Models\products;

class ProductsController extends Controller
{
    public function index()
    {
        $products = products::all();

        return response()->json($products);
    }

    public function findById($id)
    {
        $products = products::find($id);

        if (!$products) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($products);
    }

    public function search(Request $request)
    {
        $request->validate([
            'dato' => 'required|string',
        ]);

        $dato = $request->input('dato');

        $products = products::where('name', 'like', "%$dato%")->orWhere('description', 'like', "%$dato%")->get();

        return response()->json($products);
    }


    public function flavor()
    {
        return $this->belongsTo(flavors::class);
    }

    public function flavorFilter($flavor)
    {
        $products = products::whereHas('flavor', function ($query) use ($flavor) {
            $query->where('name', $flavor);
        })->get();

        if ($products->isEmpty()) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($products);
    }


    public function weightFilter($weight)
    {
        $products = products::where('weight', $weight)->get();

        if ($products->isEmpty()) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($products);
    }





    public function filterProducts(Request $request)
    {
        $products = products::query()
            ->when($request->flavor, function ($query, $flavor) {
                return $query->where('flavor_name', $flavor);
            })
            ->when($request->weight, function ($query, $weight) {
                return $query->where('weight', $weight);
            })
            ->when($request->region, function ($query, $region) {
                return $query->where('region', $region);
            })
            ->get();

        if ($products->isEmpty()) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($products);
    }





}
