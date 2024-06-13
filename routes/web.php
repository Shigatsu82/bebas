<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;



Route::group(['middleware' => 'guest'], function(){
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost']);
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function(){
        return view('dashboard');
    })->name('dashboard');
    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('products', ProductController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('inproducts', BarangMasukController::class);
    Route::resource('outproducts', BarangKeluarController::class);
});
