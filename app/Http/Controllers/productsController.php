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
        $minPrice = $request->minPrice;
        $maxPrice = $request->maxPrice;

        if ($minPrice && $maxPrice) {
            $minPrice = min($minPrice, $maxPrice);
            $maxPrice = max($minPrice, $maxPrice);
        }

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
            ->when($minPrice && $maxPrice, function ($query) use ($minPrice, $maxPrice) {
                return $query->whereBetween('price', [$minPrice, $maxPrice]);
            })
            ->get();

        foreach ($products as $product) {
            $product->price = round($product->price, 2);
        }

        if ($products->isEmpty()) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json($products);
    }




    public function getPriceRange($num1, $num2)
    {
        $minPrice = min($num1, $num2);
        $maxPrice = max($num1, $num2);

        $products = products::whereBetween('price', [$minPrice, $maxPrice])->get();

        foreach ($products as $product) {
            $product->price = round($product->price, 2);
        }

        if ($products->isEmpty()) {
            return response()->json(['error' => 'No products found in this price range'], 404);
        }

        return response()->json($products);
    }







}
