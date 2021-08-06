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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//login
Route::post('login', 'userController@logIn');
//form register data
Route::get('datos-registro', 'userController@getFormRegisterData');
//register
Route::post('registrar-usuario', 'userController@register');
//password reset
Route::post('recuperar-contraseña', 'userController@resetPassword');

Route::get('listar-usuarios', 'userActionsController@userList');

Route::group(['middleware' => 'auth:api'], function () {
    //close session
    Route::post('cerrar-sesion', 'userController@logOut');
    //users list data
    Route::get('datos-listar-usuarios', 'userActionsController@dataUsersList')->middleware('accessList');
    //users list
    Route::get('listar-usuarios', 'userActionsController@userList')->middleware('accessList');
});
