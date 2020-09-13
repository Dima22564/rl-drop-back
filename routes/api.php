<?php

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

Route::group([
  'middleware' => ['throttle:5,60'],
], function () {
  Route::post('/register', 'AuthController@register');
  Route::post('/login', 'AuthController@login');
});

Route::group([
  'middleware' => ['throttle:60,1', 'auth.jwt'],
],
  function () {
    Route::post('/logout', 'AuthController@logout');
    Route::post('/user', 'UserController@getUser');
    Route::post('/user/{id}/update', 'UserController@update');
    Route::post('/user/{id}/security/change-password', 'SecurityController@updatePassword');
    Route::post('/enable2fa', 'SecurityController@enable2fa')->name('enable2faSecret');
    Route::post('/disable2fa/{id}', 'SecurityController@disable2fa')->name('disable2fa');
    Route::post('/change-password', 'PasswordResetController@updatePassword');
    Route::post('/open-chest', 'ChestController@openChest');
    Route::post('/play-craft', 'ItemController@play');
    Route::post('/sell-item', 'ItemController@sell');

    Route::get('/inventory', 'UserController@getInventory');
  });

Route::group([
  'prefix' => 'admin'
], function () {
  Route::get('/item-types', 'Admin\ItemTypesController@index');
  Route::post('/create-item-type', 'Admin\ItemTypesController@store');
  Route::post('/create-item', 'Admin\ItemController@store');
  Route::get('/items-for-chests', 'Admin\ItemController@loadItemsForChests');
  Route::post('/create-chest', 'Admin\ChestController@store');
  Route::get('/chests-list', 'Admin\ChestController@index');
  Route::get('/items-all', 'Admin\ItemController@loadItemsAll');
  Route::delete('/delete-chest/{id}', 'Admin\ChestController@deleteById');
  Route::delete('/delete-item/{id}', 'Admin\ItemController@deleteById');
});

Route::group([
  'prefix' => 'admin/stats'
], function () {
  Route::get('/chest/{craft}', 'Admin\Stats\ChestController@index');
  Route::post('/chest-stats-between-time', 'Admin\Stats\ChestController@chestStatsBetweenTime');

  Route::get('/item-types', 'Admin\Stats\TypeController@index');
  Route::get('/craft/{craft}', 'Admin\Stats\CraftController@index');

  Route::get('/sales', 'Admin\Stats\SalesController@index');
});

Route::get('/chests-list', 'ChestController@index');
Route::get('/craft-items', 'ItemController@loadCraftItems');
Route::get('/craft-item/{id}', 'ItemController@craftItem');


Route::post('/reset-password/send-link', 'PasswordResetController@sendPasswordResetLink')->middleware('throttle:5,60');

Route::post('/reset-password/new-password', 'PasswordResetController@recoveryPassword');

Route::get('/chest/{id}', 'ChestController@chest');



