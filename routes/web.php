<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('checkout');
});

Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class,'index'])->name('checkout');



Route::get('/checkout/response', function (\Illuminate\Http\Request $request){
    return $request->all();
});
