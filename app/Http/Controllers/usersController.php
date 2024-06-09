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

    //elimina un producto del carrito con todas sus instancias
    public function removeAllOfProductFromCart($username, $productId)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $ids = explode(',', $user->cart);

        //filtra el array para eliminar todos los elementos que sean iguales al productId
        $ids = array_filter($ids, function ($id) use ($productId) {
            return $id != $productId;
        });

        //actualiza el campo cart del usuario
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

        //explode separa una cadena con la referencia del argumento introducido, por ejemplo, por comas
        $ids = explode(', ', $user->cart);

        //comprueba si el producto es válido (1-20)
        if ($productId < 1 || $productId > 20) {
            return response()->json(['error' => 'Invalid product ID'], 400);
        }

        //agrega el producto al carrito
        array_push($ids, $productId);

        //actualiza el campo cart del usuario uniendo los datos por comas
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

        //se busca en el array por los campos introducidos y devuelve el primero que se encuentra
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


    //limpia el campo de discounts del usuario concreto
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

        //obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        //comprobar si la cadena está vacía
        if (empty($giftPacksString)) {
            return response()->json(['message' => 'No gift packs found'], 404);
        }

        //limpiar y corregir el formato de la cadena y añadir corchetes para que sea un JSON válido
        $giftPacksString = '[' . $giftPacksString . ']';

        //convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        return response()->json($giftPacks, 200);
    }

    //añade un pack de regalo nuevo (vacio) al usuario 
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


    //elimina todas las instancias de un producto de un pack de regalo
    public function removeAllInstancesFromGiftPack($username, $packIndex, $productId)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        //obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        //limpiar y corregir el formato de la cadena y añadir corchetes para que sea un JSON válido
        $giftPacksString = '[' . $giftPacksString . ']';

        //convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        //comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }

        //eliminar todas las instancias del producto del pack
        $giftPacks[$packIndex] = array_values(array_filter($giftPacks[$packIndex], function ($product) use ($productId) {
            return $product != $productId;
        }));

        //convertir el array a JSON
        $newGiftPacksString = json_encode($giftPacks);

        //eliminar los corchetes exteriores del JSON y limpiar el formato
        $newGiftPacksString = substr($newGiftPacksString, 1, -1);

        //actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'All instances of product removed from gift pack'], 200);
    }




    //elimina un solo producto de un pack de regalo concreto
    public function removeProductFromGiftPack($username, $packIndex, $productId)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        //obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        //limpiar y corregir el formato de la cadena y añadir corchetes para que sea un JSON válido
        $giftPacksString = '[' . $giftPacksString . ']';

        //convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        //comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }



        //eliminar el producto del pack
        $giftPacks[$packIndex] = array_values(array_filter($giftPacks[$packIndex], function ($product) use ($productId) {
            return $product != $productId;
        }));

        //convertir el array a JSON
        $newGiftPacksString = json_encode($giftPacks);

        //eliminar los corchetes exteriores del JSON y limpiar el formato
        $newGiftPacksString = substr($newGiftPacksString, 1, -1);

        //actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'Product removed from gift pack'], 200);
    }
    public function addProductToGiftPack($username, $packIndex, $productId)
    {
        //buscar el usuario por su username
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        //obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        //limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  //añadir corchetes para que sea un JSON válido

        //lonvertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        //comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }

        //añadir el producto al pack correspondiente
        if (empty($giftPacks[$packIndex])) {
            $giftPacks[$packIndex] = [(int) $productId];
        } else {
            array_push($giftPacks[$packIndex], (int) $productId);
        }


        //convertir el array a JSON
        $newGiftPacksString = json_encode($giftPacks);

        //eliminar los corchetes exteriores del JSON y limpiar el formato
        $newGiftPacksString = substr($newGiftPacksString, 1, -1);

        //actualizar el campo gift_packs del usuario con los nuevos datos
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

        //obtener la cadena de gift_packs
        $giftPacksString = $user->gift_packs;

        //limpiar y corregir el formato de la cadena
        $giftPacksString = '[' . $giftPacksString . ']';  //añadir corchetes para que sea un JSON válido

        //convertir la cadena JSON en un array
        $giftPacks = json_decode($giftPacksString, true);

        if ($giftPacks === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid gift packs format'], 400);
        }

        //comprobar si el pack existe
        if (!isset($giftPacks[$packIndex])) {
            return response()->json(['message' => 'Pack not found'], 404);
        }



        //eliminar el producto del pack
        unset($giftPacks[$packIndex]);

        //reindexar el array para eliminar los huecos
        $giftPacks = array_values($giftPacks);

        //convertir cada subarray a una cadena
        $giftPacks = array_map(function ($pack) {
            return '[' . implode(',', $pack) . ']';
        }, $giftPacks);

        //unir todas las cadenas en una sola
        $newGiftPacksString = implode(',', $giftPacks);

        //actualizar el campo gift_packs del usuario con los nuevos datos
        $user->gift_packs = $newGiftPacksString;
        $user->save();

        return response()->json(['message' => 'Product removed from gift pack'], 200);
    }

    public function getCart($username)
    {
        $user = users::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $cart = $user->cart;

        //eliminar las comas al principio y al final de la cadena
        $cart = trim($cart, ',');

        //convertir la cadena en un array
        $cartArray = explode(',', $cart);

        //convertir las cadenas en números
        $cartArray = array_map('intval', $cartArray);

        return response()->json($cartArray);
    }

    //vacia el carrito por completo
    public function clearCart($username)
    {
        $user = users::find($username);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->cart = null;
        $user->save();

        return response()->json(['message' => 'Cart cleared successfully']);
    }


}
