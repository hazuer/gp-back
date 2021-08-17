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

//login
Route::post('login', 'userController@logIn');
//form register data
Route::get('datos-registro', 'userController@getFormRegisterData');
//register
Route::post('registrar-usuario', 'userController@register');
//password reset
Route::post('recuperar-contraseña', 'userController@resetPassword');


Route::group(['middleware' => 'auth:api'], function () {
    //close session
    Route::post('cerrar-sesion', 'userController@logOut');
    //users list data
    Route::get('datos-listar-usuarios', 'userActionsController@dataUsersList')->middleware('accessAdminSupervisor');
    //users list
    Route::get('listar-usuarios', 'userActionsController@userList')->middleware('accessAdminSupervisor');
    //user data
    Route::get('datos-usuario', 'userActionsController@userDataForm')->middleware('accessAdminSupervisor');
    //authorizer user
    Route::post('autorizar-usuarios', 'userActionsController@userAuthtorize')->middleware('accessAdminSupervisor');
    //update user data
    Route::post('editar-usuarios-datos', 'userActionsController@updateUserData')->middleware('accessAdminSupervisor');
    //deactivate user 
    Route::post('desactivar-usuarios', 'userActionsController@userDeactivate')->middleware('accessAdminSupervisor');
    //update user permissions
    //Route::post('editar-usuarios-permisos', 'userActionsController@updateUserPermissions');


    //data list countries  and status
    Route::get('catalogo/paises-estatus-listas', 'ComunFunctionsController@countriesStatusList')->middleware('accessAdminSupervisor');
    //list Plants Catalog 
    Route::get('catalogo/listar-plantas', 'catPlantsController@plantsList')->middleware('accessAdminSupervisor');
    //create plants
    Route::post('catalogo/crear-plantas', 'catPlantsController@registerPlant')->middleware('accessAdminSupervisor');
    //update plant
    Route::post('catalogo/editar-plantas', 'catPlantsController@updatePlant');
    //active deactive and delete plant
    Route::post('catalogo/editar-estatus-planta', 'catPlantsController@activeDeactiveDeletePlant');


    //data list plants  and status
    Route::get('catalogo/plantas-estatus-listas', 'ComunFunctionsController@plantsStatusList')->middleware('accessAdminSupervisor');
    //list Country Catalog
    Route::get('catalogo/listar-razones', 'catReasonsController@reasonsList')->middleware('accessAdminSupervisor');
    //create Country
    Route::post('catalogo/crear-razones', 'catReasonsController@registerReason')->middleware('accessAdminSupervisor');
    //update Country
    Route::post('catalogo/editar-razones', 'catReasonsController@updateReason');
    //active deactive and delete Country
    Route::post('catalogo/editar-estatus-razones', 'catReasonsController@activeDeactiveDeleteReazon');



    //list Country Catalog
    Route::get('catalogo/listar-paises', 'catCountriesController@countryList')->middleware('accessAdminSupervisor');
    //create Country
    Route::post('catalogo/crear-paises', 'catCountriesController@registerCountry');
    //update Country
    Route::post('catalogo/editar-paises', 'catCountriesController@updateCountry');
    //active deactive and delete Country
    Route::post('catalogo/editar-estatus-paises', 'catCountriesController@activeDeactiveDeleteCountry');
});
