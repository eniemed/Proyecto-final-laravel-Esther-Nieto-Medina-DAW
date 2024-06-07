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

        // Obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        // Comprobar si la cadena está vacía
        if (empty($giftPacksString)) {
            return response()->json(['message' => 'No gift packs found'], 404);
        }

        // Limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  // Añadir corchetes para que sea un JSON válido

        // Convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        return response()->json($giftPacks, 200);
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


    public function removeAllInstancesFromGiftPack($username, $packIndex, $productId)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        // Limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  // Añadir corchetes para que sea un JSON válido

        // Convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        // Comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }

        // Eliminar todas las instancias del producto del pack
        $giftPacks[$packIndex] = array_values(array_filter($giftPacks[$packIndex], function ($product) use ($productId) {
            return $product != $productId;
        }));

        // Convertir el array a JSON
        $newGiftPacksString = json_encode($giftPacks);

        // Eliminar los corchetes exteriores del JSON y limpiar el formato
        $newGiftPacksString = substr($newGiftPacksString, 1, -1);

        // Actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'All instances of product removed from gift pack'], 200);
    }





    public function removeProductFromGiftPack($username, $packIndex, $productId)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        // Limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  // Añadir corchetes para que sea un JSON válido

        // Convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        // Comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }



        // Eliminar el producto del pack
        $giftPacks[$packIndex] = array_values(array_filter($giftPacks[$packIndex], function ($product) use ($productId) {
            return $product != $productId;
        }));

        // Convertir el array a JSON
        $newGiftPacksString = json_encode($giftPacks);

        // Eliminar los corchetes exteriores del JSON y limpiar el formato
        $newGiftPacksString = substr($newGiftPacksString, 1, -1);

        // Actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'Product removed from gift pack'], 200);
    }
    public function addProductToGiftPack($username, $packIndex, $productId)
    {
        // Buscar el usuario por su username
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        // Limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  // Añadir corchetes para que sea un JSON válido

        // Convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        // Comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }

        // Añadir el producto al pack correspondiente
        if (empty($giftPacks[$packIndex])) {
            $giftPacks[$packIndex] = [(int) $productId];
        } else {
            array_push($giftPacks[$packIndex], (int) $productId);
        }


        // Convertir el array a JSON
        $newGiftPacksString = json_encode($giftPacks);

        // Eliminar los corchetes exteriores del JSON y limpiar el formato
        $newGiftPacksString = substr($newGiftPacksString, 1, -1);

        // Actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'Product added to gift pack'], 200);
    }

    public function removeGiftPack($username, $packIndex)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        // Limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  // Añadir corchetes para que sea un JSON válido

        // Convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        // Comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }



        // Eliminar el producto del pack
        unset($giftPacks[$packIndex]);

        // Reindexar el array para eliminar cualquier brecha en los índices
        $giftPacks = array_values($giftPacks);

        // Convertir cada subarray a una cadena
        $giftPacks = array_map(function ($pack) {
            return '[' . implode(',', $pack) . ']';
        }, $giftPacks);

        // Unir todas las cadenas en una sola
        $newGiftPacksString = implode(',', $giftPacks);

        // Actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'Product removed from gift pack'], 200);
    }

}
