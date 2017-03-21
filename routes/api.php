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

Route::post('/login',[
    'uses'=>'Api\Auth\LoginController@authenticate'
]);

Route::post('/register',[
    'uses'=>'Api\Auth\RegisterController@register'
]);

Route::get('/', function (Request $request) {
    return response()->json(
        \App\Models\Access\User\User
            ::with('grantedRoles')
            ->with('deniedRoles')
            ->with('grantedPermissions')
            ->with('deniedPermissions')
            ->get()
    );
})->middleware('jwt.auth');
