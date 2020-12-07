<?php

use App\Http\Controllers\ShopifyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/shopify/auth', [ShopifyController::class, 'auth']);
Route::get('/shopify/token', [ShopifyController::class, 'token']);

Route::get('/oauth/authorize', function() {
   return 'aaatest';
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
