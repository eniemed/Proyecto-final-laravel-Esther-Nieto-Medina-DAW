<?php

use App\Http\Controllers\flavorsController;
use App\Http\Controllers\usersController;
use App\Http\Controllers\suppliersController;
use App\Http\Controllers\productsController;
use App\Http\Controllers\ordersController;
use Illuminate\Support\Facades\Route;



Route::get("/flavors", [flavorsController::class,"index"]);
Route::get("/users", [usersController::class,"index"]);
Route::get('/user/{username}', [usersController::class,"getUser"]);
Route::get("/suppliers", [suppliersController::class,"index"]);
Route::get("/products", [productsController::class,"index"]);
Route::get("/orders", [ordersController::class,"index"]);
Route::get('/products/search', [productsController::class, "search"]);
Route::post('/signup', [usersController::class, "signup"]);
Route::get('/products/weight/{weight}', [productsController::class, 'weightFilter']);
Route::get('/login', [usersController::class, "login"]);
Route::get('/wishlist', [usersController::class, "wishlist"]);
Route::get('/products/filter', [productsController::class, 'filterProducts']);
Route::get('/products/flavors/{flavor}', [productsController::class, 'flavorFilter']);
Route::put('/wishlist/{username}', [usersController::class, "removeFromWishlist"]);
Route::get('/user/{username}/cart', [usersController::class, "getCart"]);
Route::post('/user/{username}/clear-cart', [usersController::class, "clearCart"]);

Route::post('/user/{username}/clear-discount', [usersController::class, "clearUserDiscounts"]);

Route::get('cart/{username}/products', [usersController::class, 'getProducts']);
Route::delete('/user/{username}/cart/{productId}', [usersController::class, 'removeProductFromCart']);
Route::post('/user/{username}/cart/{productId}', [usersController::class, 'addProductToCart']);
Route::delete('/user/{username}/cart/{productId}/all', [usersController::class, 'removeAllOfProductFromCart']);

Route::get('/orders/{username}', [ordersController::class, 'getOrders']);
Route::post('/user/{username}/add-order', [ordersController::class, 'addOrder']);

Route::get('/users/{username}/gift-packs', [usersController::class, 'getGiftPacks']);
Route::post('/users/{username}/gift-packs', [usersController::class, 'addGiftPack']);
Route::delete('/users/{username}/gift-packs/{packIndex}/products/{productId}', [usersController::class, 'removeProductFromGiftPack']);
Route::delete('/user/{username}/gift-packs/{packId}/product/{number}', [usersController::class, 'removeAllInstancesFromGiftPack']);
Route::post('/user/{username}/add/{packIndex}/product/{productId}', [usersController::class, 'addProductToGiftPack']);
Route::delete('/user/{username}/pack/{packIndex}', [usersController::class, 'removeGiftPack']);

Route::delete('/wishlist/{username}/clear', [usersController::class, "clearWishlist"]);
Route::put('/wishlist/{username}/add/{id}', [usersController::class, 'addToWishlist']);
Route::get('/{id}', [productsController::class, "findById"]);








