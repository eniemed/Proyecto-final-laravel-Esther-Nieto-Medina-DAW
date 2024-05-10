<?php

namespace App\Http\Controllers;

use App\Models\flavors;
use App\Models\products;

class flavorsController extends Controller
{
    public function index()
    {
        $flavors = flavors::all();

        return response()->json($flavors);
    }


    public function flavorFilter($flavor)
    {
        $flavor = flavors::where('name', $flavor)->first();

        if (!$flavor) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $products = $flavor->products;

        return response()->json($products);
    }

    public function products()
    {
        return $this->hasMany(products::class);
    }


}
