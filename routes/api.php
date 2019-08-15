<?php

use Illuminate\Http\Request;

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


Route::middleware('auth:api')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::resource('cities', 'API\CityController')->except(['index']);
    Route::resource('categories', 'API\CategoryController')->except(['index']);
    Route::resource('restaurants', 'API\RestaurantController')->except(['index']);
    Route::resource('menus', 'API\MenuController')->except(['index']);
    Route::resource('items', 'API\ItemController')->except(['index']);
    Route::post('/cities/insertmany', 'API\CityController@insertMany');
});

Route::resource('cities', 'API\CityController')->only(['index']);
Route::resource('categories', 'API\CategoryController')->only(['index']);
Route::resource('restaurants', 'API\RestaurantController')->only(['index']);
Route::resource('menus', 'API\MenuController')->only(['index']);
Route::resource('items', 'API\ItemController')->only(['index']);
