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


    public function removeAllOfProductFromCart($username, $productId)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $ids = explode(',', $user->cart);

        // Filtra el array para eliminar todos los elementos que sean iguales al productId
        $ids = array_filter($ids, function ($id) use ($productId) {
            return $id != $productId;
        });

        // Actualiza el campo cart del usuario
        $user->cart = implode(',', $ids);
        $user->save();

        return response()->json(['message' => 'Product removed from cart']);
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



    public function addProductToCart($username, $productId)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $ids = explode(', ', $user->cart);

        // Comprueba si el producto es válido (1-20)
        if ($productId < 1 || $productId > 20) {
            return response()->json(['error' => 'Invalid product ID'], 400);
        }

        // Agrega el producto al carrito
        array_push($ids, $productId);

        // Actualiza el campo cart del usuario
        $user->cart = implode(',', $ids);
        $user->save();

        return response()->json(['message' => 'Product added to cart']);
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

    public function removeProductFromCart($username, $productId)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $ids = explode(',', $user->cart);

        $index = array_search($productId, $ids);

        if ($index !== false) {
            unset($ids[$index]);
        }

        $user->cart = implode(',', $ids);
        $user->save();

        return response()->json(['message' => 'Product removed from cart']);
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

    public function getUser($username)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function clearUserDiscounts($username)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->discounts = null;
        $user->save();

        return response()->json(['message' => 'Discounts cleared successfully']);
    }

    public function getGiftPacks($username)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // aqui divido la cadena gift_packs en múltiples cadenas, cada una conteniendo un solo objeto JSON
        $giftPacksStrings = explode('],[', $user->gift_packs);

        $giftPacks = [];
        foreach ($giftPacksStrings as $giftPackString) {
            if ($giftPackString[0] != '[') {
                $giftPackString = '[' . $giftPackString;
            }
            if ($giftPackString[-1] != ']') {
                $giftPackString = $giftPackString . ']';
            }

            $giftPack = json_decode($giftPackString, true);
            $giftPacks[] = $giftPack;
        }

        return response()->json($giftPacks);
    }

    public function addGiftPack($username)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (substr($user->gift_packs, -1) === ']') {
            $user->gift_packs .= ',[]';
        } else {
            $user->gift_packs .= '[]';
        }

        $user->save();

        return response()->json(['message' => 'Gift pack added successfully']);
    }



}

