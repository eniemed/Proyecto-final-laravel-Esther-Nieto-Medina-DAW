<?php

use App\Http\Controllers\flavorsController;
use App\Http\Controllers\usersController;
use App\Http\Controllers\suppliersController;
use App\Http\Controllers\productsController;
use App\Http\Controllers\ordersController;
use Illuminate\Support\Facades\Route;



Route::get("/flavors", [flavorsController::class,"index"]);
Route::get("/users", [usersController::class,"index"]);
Route::get("/suppliers", [suppliersController::class,"index"]);
Route::get("/products", [productsController::class,"index"]);
Route::get("/orders", [ordersController::class,"index"]);
Route::get('/products/search', [productsController::class, "search"]);
Route::post('/signup', [usersController::class, "signup"]);
Route::get('/products/weight/{weight}', [ProductsController::class, 'weightFilter']);
Route::get('/login', [usersController::class, "login"]);
Route::get('/wishlist', [usersController::class, "wishlist"]);
Route::get('/products/filter', [productsController::class, 'filterProducts']);
Route::get('/products/flavors/{flavor}', [productsController::class, 'flavorFilter']);
Route::put('/wishlist/{username}', [usersController::class, "removeFromWishlist"]);
Route::delete('/wishlist/{username}/clear', [usersController::class, "clearWishlist"]);
Route::put('/wishlist/{username}/add/{id}', [usersController::class, 'addToWishlist']);
Route::get('/{id}', [productsController::class, "findById"]);








