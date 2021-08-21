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



    //list Countries Catalog
    Route::get('catalogo/listar-paises', 'catCountriesController@countryList')->middleware('accessAdminSupervisor');
    //create Country
    Route::post('catalogo/crear-paises', 'catCountriesController@registerCountry');
    //update Country
    Route::post('catalogo/editar-paises', 'catCountriesController@updateCountry');
    //active deactive and delete Country
    Route::post('catalogo/editar-estatus-paises', 'catCountriesController@activeDeactiveDeleteCountry');


    //list Customers Catalog
    Route::get('catalogo/listar-clientes', 'catCustomersController@customersList')->middleware('accessAdminSupervisor');
    //create Customer
    Route::post('catalogo/crear-clientes', 'catCustomersController@registerCustomer');
    //update Customer
    Route::post('catalogo/editar-clientes', 'catCustomersController@updateCustomer');
    //active deactive and delete Customer
    Route::post('catalogo/editar-estatus-clientes', 'catCustomersController@activeDeactiveDeleteCustomer');



    //list Machines Catalog
    Route::get('catalogo/listar-maquinas', 'catMachinesController@machinesList')->middleware('accessAdminSupervisor');
    //create Machine
    Route::post('catalogo/crear-maquinas', 'catMachinesController@registerMachine');
    //update Machine
    Route::post('catalogo/editar-maquinas', 'catMachinesController@updateMachine');
    //active deactive and delete Machine
    Route::post('catalogo/editar-estatus-maquinas', 'catMachinesController@activeDeactiveDeleteMachine');


    //list Taras Catalog
    Route::get('catalogo/listar-taras', 'catTarasController@tarasList')->middleware('accessAdminSupervisor');
    //create Tara
    Route::post('catalogo/crear-taras', 'catTarasController@registerTara');
    //update Tara
    Route::post('catalogo/editar-taras', 'catTarasController@updateTara');
    //active deactive and delete Tara
    Route::post('catalogo/editar-estatus-taras', 'catTarasController@activeDeactiveDeleteTara');


    //list Inks Catalog
    Route::get('catalogo/listar-tintas', 'catInksControllers@inksList')->middleware('accessAdminSupervisor');
    //create Ink
    Route::post('catalogo/crear-tintas', 'catInksControllers@registerInk');
    //update Ink
    Route::post('catalogo/editar-tintas', 'catInksControllers@updateInk');
    //active deactive and delete Ink
    Route::post('catalogo/editar-estatus-tintas', 'catInksControllers@activeDeactiveDeleteInk');
    //import ink csv
    Route::post('catalogo/importar-tintas-csv', 'catInksControllers@importInkCSV');


    //list Designs Catalog
    Route::get('catalogo/listar-disenos', 'catDesignController@designsList')->middleware('accessAdminSupervisor');
    //Ink search to designs
    Route::post('catalogo/buscar-tintas', 'catDesignController@inkSearch');
    //create design
    Route::post('catalogo/crear-disenos', 'catDesignController@registerDesign');
    //import ink csv
    Route::post('catalogo/importar-disenos-csv', 'catDesignController@importDesignCSV');
    //one desing data
    Route::get('catalogo/datos-diseno', 'catDesignController@designData')->middleware('accessAdminSupervisor');
    //update  desing
    Route::post('catalogo/editar-disenos', 'catDesignController@updateDesign');
    //update status inks design
    Route::post('catalogo/editar-estatus-tinta-diseno', 'catDesignController@updateStatusInksDesign');
    //active deactive and delete design
    Route::post('catalogo/editar-estatus-disenos', 'catDesignController@activeDeactiveDeleteDesign');
});
