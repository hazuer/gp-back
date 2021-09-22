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
    Route::get('datos-listar-usuarios', 'userActionsController@dataUsersList')->middleware('accessAdmin');
    //users list
    Route::get('listar-usuarios', 'userActionsController@userList')->middleware('accessAdmin');
    //user data
    Route::get('datos-usuario', 'userActionsController@userDataForm')->middleware('accessAdmin');
    //authorizer user
    Route::post('autorizar-usuarios', 'userActionsController@userAuthtorize');
    //update user data
    Route::post('editar-usuarios-datos', 'userActionsController@updateUserData');
    //deactivate user 
    Route::post('desactivar-usuarios', 'userActionsController@userDeactivate');
    //update user permissions
    //Route::post('editar-usuarios-permisos', 'userActionsController@updateUserPermissions');


    //data list countries  and status
    Route::get('catalogo/paises-estatus-listas', 'ComunFunctionsController@countriesStatusList')->middleware('accessAdmin');
    //list Plants Catalog 
    Route::get('catalogo/listar-plantas', 'catPlantsController@plantsList')->middleware('accessAdmin');
    //create plants
    Route::post('catalogo/crear-plantas', 'catPlantsController@registerPlant');
    //update plant
    Route::post('catalogo/editar-plantas', 'catPlantsController@updatePlant');
    //active deactive and delete plant
    Route::post('catalogo/editar-estatus-planta', 'catPlantsController@activeDeactiveDeletePlant');


    //data list plants  and status
    Route::get('catalogo/plantas-estatus-listas', 'ComunFunctionsController@plantsStatusList')->middleware('accessAdmin');
    //list Country Catalog
    Route::get('catalogo/listar-razones', 'catReasonsController@reasonsList')->middleware('accessAdmin');
    //create Country
    Route::post('catalogo/crear-razones', 'catReasonsController@registerReason');
    //update Country
    Route::post('catalogo/editar-razones', 'catReasonsController@updateReason');
    //active deactive and delete Country
    Route::post('catalogo/editar-estatus-razones', 'catReasonsController@activeDeactiveDeleteReazon');



    //list Countries Catalog
    Route::get('catalogo/listar-paises', 'catCountriesController@countryList')->middleware('accessAdmin');
    //create Country
    Route::post('catalogo/crear-paises', 'catCountriesController@registerCountry');
    //update Country
    Route::post('catalogo/editar-paises', 'catCountriesController@updateCountry');
    //active deactive and delete Country
    Route::post('catalogo/editar-estatus-paises', 'catCountriesController@activeDeactiveDeleteCountry');


    //list Customers Catalog
    Route::get('catalogo/listar-clientes', 'catCustomersController@customersList')->middleware('accessAdmin');
    //create Customer
    Route::post('catalogo/crear-clientes', 'catCustomersController@registerCustomer');
    //update Customer
    Route::post('catalogo/editar-clientes', 'catCustomersController@updateCustomer');
    //active deactive and delete Customer
    Route::post('catalogo/editar-estatus-clientes', 'catCustomersController@activeDeactiveDeleteCustomer');



    //list Machines Catalog
    Route::get('catalogo/listar-maquinas', 'catMachinesController@machinesList')->middleware('accessAdmin');
    //create Machine
    Route::post('catalogo/crear-maquinas', 'catMachinesController@registerMachine');
    //update Machine
    Route::post('catalogo/editar-maquinas', 'catMachinesController@updateMachine');
    //active deactive and delete Machine
    Route::post('catalogo/editar-estatus-maquinas', 'catMachinesController@activeDeactiveDeleteMachine');


    //list Taras Catalog
    Route::get('catalogo/listar-taras', 'catTarasController@tarasList')->middleware('accessAdmin');
    //create Tara
    Route::post('catalogo/crear-taras', 'catTarasController@registerTara');
    //update Tara
    Route::post('catalogo/editar-taras', 'catTarasController@updateTara');
    //active deactive and delete Tara
    Route::post('catalogo/editar-estatus-taras', 'catTarasController@activeDeactiveDeleteTara');


    //list Inks Catalog
    Route::get('catalogo/listar-tintas', 'catInksControllers@inksList')->middleware('accessAdmin');
    //create Ink
    Route::post('catalogo/crear-tintas', 'catInksControllers@registerInk');
    //update Ink
    Route::post('catalogo/editar-tintas', 'catInksControllers@updateInk');
    //active deactive and delete Ink
    Route::post('catalogo/editar-estatus-tintas', 'catInksControllers@activeDeactiveDeleteInk');
    //import ink csv
    Route::put('catalogo/importar-tintas-csv', 'catInksControllers@importInkCSV');


    //list Designs Catalog
    Route::get('catalogo/listar-disenos', 'catDesignController@designsList')->middleware('accessAdmin');
    //Ink search to designs
    Route::post('catalogo/buscar-tintas', 'catDesignController@inkSearch');
    //create design
    Route::post('catalogo/crear-disenos', 'catDesignController@registerDesign');
    //import ink csv
    Route::put('catalogo/importar-disenos-csv', 'catDesignController@importDesignCSV');
    //one desing data
    Route::get('catalogo/datos-diseno', 'catDesignController@designData')->middleware('accessAdmin');
    //update  desing
    Route::post('catalogo/editar-disenos', 'catDesignController@updateDesign');
    //update status inks design
    Route::post('catalogo/editar-estatus-tinta-diseno', 'catDesignController@updateStatusInksDesign');
    //active deactive and delete design
    Route::post('catalogo/editar-estatus-disenos', 'catDesignController@activeDeactiveDeleteDesign');


    //administration
    //get system params
    Route::get('administracion/parametrizar-sistema', 'systemAdministrationController@getSystemParams')->middleware('accessAdmin');
    //system params register and update
    Route::post('administracion/parametrizar-sistema-crear-editar', 'systemAdministrationController@registerUpdateSystemParams');


    //delivery Order 
    //list delivery resources
    Route::get('orden-de-entrega/listas-busqueda-oe', 'deliveryOrdersController@deliveryOrderDataToList')->middleware('AccessOE');
    //list delivery orders 
    Route::get('orden-de-entrega/listar', 'deliveryOrdersController@deliveryOrdersList')->middleware('AccessOE');
    // delivery orders register resoucres
    Route::get('orden-de-entrega/registrar-oe-recursos', 'deliveryOrdersController@registerDeliveryOrderResources')->middleware('AccessOE');
    //delivery orders get inks desing
    Route::get('orden-de-entrega/diseno-tintas', 'deliveryOrdersController@getInkDesing')->middleware('AccessOE');
    //delivery orders create
    Route::post('orden-de-entrega/crear', 'deliveryOrdersController@registerdeliveryOrder');
    //get delivery order
    Route::get('orden-de-entrega/obtener-oe', 'deliveryOrdersController@getDeliveryOrder')->middleware('AccessOE');
    //update delivery order
    Route::post('orden-de-entrega/editar', 'deliveryOrdersController@updateDeliveryOrder');


    //receptions
    //receptions list 
    Route::get('orden-de-entrega-recepcion/listar', 'deliveryOrdersController@deliveryOrdersList')->middleware('AccessReceptions');
});
