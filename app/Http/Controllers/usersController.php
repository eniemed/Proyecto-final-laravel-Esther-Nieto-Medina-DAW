<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\users;
use App\Models\products;

class usersController extends Controller
{
    public function index()
    {
        $users = users::all();

        return response()->json($users);
    }

    public function signup(Request $request)
    {

        $existingUser = users::where('email', $request->input('email'))->orWhere('username', $request->input('username'))->first();

        if ($existingUser) {
            return response()->json(['message' => 'El usuario ya existe'], 400);
        }

        $user = users::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'order_id' => null,
            'wishlist' => null,
        ]);

        return response()->json(['message' => 'Your account was created succesfully!', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $user = users::where('username', $request->input('username'))->first();

        if ($user && $request->input('password') === $user->password) {
            return response()->json(['user' => $user]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }




    public function getProducts($username)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $ids = explode(',', $user->cart);

        $products = products::findMany($ids);

        return response()->json(['products' => $products]);
    }


    public function wishlist(Request $request)
    {
        $user = users::where('username', $request->input('username'))->first();

        if (!$user) {
            return response()->json(['error' => 'User no encontrado'], 404);
        }

        $wishlist = $user->wishlist;

        return response()->json($wishlist);
    }

    public function removeFromWishlist(Request $request, $username)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User no encontrado'], 404);
        }

        $wishlist = $user->wishlist;
        $idToRemove = $request->input('id');
        $wishlistArray = explode(', ', $wishlist);
        $key = array_search($idToRemove, $wishlistArray);
        if ($key !== false) {
            unset($wishlistArray[$key]);
        }
        $updatedWishlist = implode(', ', $wishlistArray);
        $user->wishlist = $updatedWishlist;
        $user->save();

        return response()->json(['message' => 'Producto eliminado de la wishlist', 'wishlist' => $updatedWishlist]);
    }

    public function clearWishlist($username)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User no encontrado'], 404);
        }

        $user->wishlist = null;
        $user->save();

        return response()->json(['message' => 'Wishlist vaciada con éxito']);
    }

    public function addToWishlist($username, $productId)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $wishlist = $user->wishlist;

        if (empty($wishlist)) {
            $wishlist = $productId;
        } else {
            $wishlist .= ", " . $productId;
        }

        $user->wishlist = $wishlist;
        $user->save();

        return response()->json(['message' => 'Product added to wishlist successfully']);
    }




}

