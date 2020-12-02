<?php

use App\Http\Middleware\Localization;
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
  'middleware' => ['throttle:60,60'],
], function () {
  Route::post('/register', 'AuthController@register');
  Route::post('/login', 'AuthController@login')->middleware(\App\Http\Middleware\Google2FAMiddleware::class);
});

Route::group([
  'middleware' => ['throttle:60,1', 'auth.jwt'],
],
  function () {
    Route::post('/logout', 'AuthController@logout');
    Route::post('/user', 'UserController@getUser');
    Route::get('/user/stats', 'UserController@getStats');
    Route::post('/user/{id}/update', 'UserController@sendUpdateLink');
    Route::post('/enable2fa', 'SecurityController@enable2fa')->name('enable2faSecret');
    Route::post('/disable2fa/{id}', 'SecurityController@disable2fa')->name('disable2fa');
    Route::post('/change-password', 'PasswordResetController@sendConfirmEmailForUpdatePassword');
    Route::post('/open-chest', 'ChestController@openChest');
    Route::post('/play-craft', 'ItemController@play');
    Route::post('/sell-item', 'ItemController@sell');



    Route::get('/read-notification/{id}', 'UserController@readNotification');
    Route::get('/notifications/read-all', 'UserController@readAllNotifications');

    Route::get('/inventory', 'UserController@getInventory');
    Route::post('/user/change-photo', 'UserController@changePhoto');

    Route::post('/user/links', 'UserController@links');

    Route::post('/user/withdraw', 'WithdrawController@withdraw');
  });

Route::group([
  'prefix' => 'admin',
  'middleware' => ['auth.jwt']
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
  Route::post('/create-faq', 'FaqController@store');

  Route::get('/item/{id}', 'Admin\ItemController@getById');
  Route::post('/item/update/{id}', 'Admin\ItemController@update');

  Route::get('/chest/{id}', 'Admin\ChestController@getById');
  Route::post('/chest/update/{id}', 'Admin\ChestController@update');

  Route::get('/users', 'Admin\UserController@index');
  Route::post('/users/add-role', 'Admin\UserController@addRole');
  Route::post('/users/remove-role', 'Admin\UserController@removeRole');

  Route::get('/withdraws', 'Admin\WithdrawController@index');
  Route::post('/withdraws/take', 'Admin\WithdrawController@take');
  Route::post('/withdraws/deny', 'Admin\WithdrawController@deny');
  Route::post('/withdraws/withdraw', 'Admin\WithdrawController@withdraw');

  Route::post('/index-top-banner', 'Admin\IndexTopBannerController@update');
  Route::get('/index-top-banner', 'Admin\IndexTopBannerController@index');

  Route::post('/index-bottom-banner', 'Admin\IndexBottomBannerController@update');
  Route::get('/index-bottom-banner', 'Admin\IndexBottomBannerController@index');

});

Route::group([
  'prefix' => 'admin/stats',
  'middleware' => ['auth.jwt']
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
Route::get('/user/{id}', 'UserController@index');

Route::get('/stats', 'AllController@index');


Route::post('/reset-password/send-link', 'PasswordResetController@sendPasswordResetLink')->middleware('throttle:5,60');

Route::post('/reset-password/new-password', 'PasswordResetController@recoveryPassword');

Route::get('/chest/{id}', 'ChestController@chest');

Route::get('/faqs', 'FaqController@index')->middleware(Localization::class);

Route::get('/win-items', 'AllController@winItems');

Route::post('/password-update', 'PasswordResetController@updatePassword');

Route::post('/user/confirm', 'UserController@update');


