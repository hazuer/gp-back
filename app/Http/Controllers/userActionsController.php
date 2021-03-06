<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\userData;
use App\Models\catCustomers;
use App\Models\catPlants;
use App\Models\catProfiles;
use App\Models\catStatus;
use App\Models\orderWork;

use App\Mail\resetPassword;
use App\Http\Controllers\ComunFunctionsController;

use App\Http\Requests\Users\AuthorizerRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\UserDeactivateRequest;
use App\Http\Requests\Users\UserPermissionRequest;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class userActionsController extends Controller
{


    //function data form list users
    public function dataUsersList(Request $req)
    {
        try {

            $listCustomers = catCustomers::select('id_cat_cliente', 'nombre_cliente')->where('id_cat_estatus', 1)->get();
            $listPlants = catPlants::select('id_cat_planta', 'nombre_planta')->where('id_cat_estatus', 1)->get();
            $listProfiles = catProfiles::select('id_cat_perfil', 'perfil')->where('id_cat_estatus', 1)->get();
            $listStatus = catStatus::all();

            if (auth()->user()->id_cat_perfil == 4) {
                $listCustomers = [];
                $listPlants = [];
            }

            return response()->json([
                'result' => true,
                'listCustomers' => $listCustomers,
                'listPlants' => $listPlants,
                'listProfiles' => $listProfiles,
                'listStatus' => $listStatus,
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function user list
    public function userList(Request $req)
    {

        try {

            $query = userData::leftJoin('usuario', 'usuario.id_dato_usuario', '=', 'datos_usuario.id_dato_usuario')
                ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', '=', 'usuario.id_cat_cliente')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', '=', 'usuario.id_cat_planta')
                ->leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', '=', 'usuario.id_cat_estatus')
                ->leftJoin('cat_perfil', 'cat_perfil.id_cat_perfil', '=', 'usuario.id_cat_perfil')

                ->select(
                    'datos_usuario.id_dato_usuario',
                    'usuario.correo',
                    'datos_usuario.nombre',
                    'datos_usuario.apellido_paterno',
                    'datos_usuario.apellido_materno',
                    'cat_estatus.estatus',
                    'cat_perfil.perfil',
                    'cat_planta.nombre_planta',
                    'cat_cliente.nombre_cliente'
                );


            //if search contain name
            if ($req->has('nombre') && !is_null($req->nombre)) {
                $query->orWhereRaw("datos_usuario.nombre  LIKE '%" . $req->nombre . "%'");
            }
            //if search contain last name
            if ($req->has('apellido_paterno') && !is_null($req->apellido_paterno)) {
                $query->orWhereRaw("datos_usuario.apellido_paterno  LIKE '%" . $req->apellido_paterno . "%'");
            }
            //if search contain secondary last name
            if ($req->has('apellido_materno')  && !is_null($req->pellido_materno)) {
                $query->orWhereRaw("datos_usuario.apellido_materno  LIKE '%" . $req->pellido_materno . "%'");
            }
            //if search contain profile type
            if ($req->has('id_cat_perfil') && !is_null($req->id_cat_perfil)) {
                $query->orWhere('usuario.id_cat_perfil', '=', $req->id_cat_perfil);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->Where('usuario.id_cat_estatus', '=', $req->id_cat_estatus);
            }

            //valid  type perfil
            switch (auth()->user()->id_cat_perfil) {
                case 1:
                    //if search contain plant
                    if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                        $query->orWhere('usuario.id_cat_planta', $req->id_cat_planta);
                    }
                    //if serach contain cliente
                    if ($req->has('id_cat_cliente') && !is_null($req->id_cat_cliente)) {
                        $query->orWhere('usuario.id_cat_cliente', $req->id_cat_cliente);
                    }
                    break;
                case 4:
                    //if user is supervisor just can see your own plants and customers
                    $query->orWhere('usuario.id_cat_planta', '=', auth()->user()->id_cat_planta)
                        ->orwhere('usuario.id_cat_cliente', '=', auth()->user()->id_cat_cliente);
                    break;
                default:
            }



            //method sort
            $direction  = "ASC";
            //if request has orderBy 
            $sortField = $req->has('ordenarPor') && !is_null($req->ordenarPor) ? $req->ordenarPor : 'estatus';

            if (Str::of($sortField)->startsWith('-')) {
                $direction  = "DESC";
                $sortField = Str::of($sortField)->substr(1);
            }
            switch ($sortField) {
                case 'nombre':
                    $sortField = "datos_usuario.nombre";
                    break;
                case 'apellido_parterno':
                    $sortField = "datos_usuario.apellido_parterno";
                    break;
                case 'apellido_marterno':
                    $sortField = "datos_usuario.apellido_marterno";
                    break;
                case 'estatus':
                    $sortField = "cat_estatus.estatus";
                    break;
                case 'perfil':
                    $sortField = "cat_perfil.perfil";
                    break;
                case 'nombre_planta':
                    $sortField = "cat_planta.nombre_planta";
                    break;
                case 'nombre_cliente':
                    $sortField = "cat_cliente.nombre_cliente";
                    break;
            }

            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $usersTotal = $query->count(); //total rows
            $usersList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'usersTotal' =>  $usersTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($usersTotal / $perPage),
                'userList' => $usersList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function data form authorize
    public function userDataForm(Request $req)
    {

        try {
            //get id from url
            $id_dato_usuario = $req->id_dato_usuario;

            //get list 
            $listCustomers = catCustomers::select('id_cat_cliente', 'nombre_cliente')->get();
            $listPlants = catPlants::select('id_cat_planta', 'nombre_planta')->get();
            $listProfiles = catProfiles::select('id_cat_perfil', 'perfil')->get();
            $listStatus = catStatus::all();

            //get user data
            $user = userData::leftJoin('usuario', 'usuario.id_dato_usuario', '=', 'datos_usuario.id_dato_usuario')
                ->select(
                    'usuario.id_dato_usuario',
                    'usuario.correo',
                    'datos_usuario.nombre',
                    'datos_usuario.apellido_paterno',
                    'datos_usuario.apellido_materno',
                    'usuario.id_cat_perfil',
                    'usuario.id_cat_cliente',
                    'usuario.id_cat_planta',
                    'usuario.id_cat_estatus'
                )
                ->where('usuario.id_dato_usuario', $id_dato_usuario)
                ->first();


            return response()->json([
                'result' => true,
                'user' => $user,
                'listCustomers' => $listCustomers,
                'listPlants' => $listPlants,
                'listProfiles' => $listProfiles,
                'listStatus' => $listStatus,
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //user Authtorize
    public function userAuthtorize(AuthorizerRequest $req)
    {

        DB::beginTransaction();

        try {
            //generate password 
            $password = (new  ComunFunctionsController)->generatePassword();

            //update data
            User::where('id_dato_usuario', $req->id_dato_usuario)
                ->update(
                    [
                        'correo' => $req->correo,
                        'password' => Hash::make($password),
                        'id_cat_planta' => $req->id_cat_planta,
                        'id_cat_cliente' => $req->id_cat_cliente,
                        'id_cat_estatus' => $req->id_cat_estatus,
                        'id_cat_perfil' => $req->id_cat_perfil,
                        'id_usuario_crea' => auth()->user()->id_usuario,
                        'fecha_creacion' => Carbon::now()->format('Y-m-d H:i:s')

                    ]
                );
            userData::where('id_dato_usuario', $req->id_dato_usuario)
                ->update(
                    [
                        'nombre' => $req->nombre,
                        'apellido_paterno' => $req->apellido_paterno,
                        'apellido_materno' => $req->apellido_materno,
                    ]
                );


            //send email 
            $subject = 'Contrase??a General Products';
            Mail::to($req->correo)->send(new resetPassword($password, $subject));


            DB::commit();

            return response()->json([
                'result' => true,
                'message' => "Usuario Autorizado con ??xito"
            ], 201);
        } catch (\Exception $exception) {
            DB::rollback();
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //update user data function 
    public function updateUserData(UpdateUserRequest $req)
    {
        try {

            User::where('id_dato_usuario', $req->id_dato_usuario)
                ->update(
                    [
                        'correo' => $req->correo,
                        'id_cat_planta' => $req->id_cat_planta,
                        'id_cat_cliente' => $req->id_cat_cliente,
                        'id_cat_estatus' => $req->id_cat_estatus,
                        'id_cat_perfil' => $req->id_cat_perfil,
                        'id_usuario_modifica' => auth()->user()->id_usuario,
                        'fecha_modificacion' => Carbon::now()->format('Y-m-d H:i:s')
                    ]
                );

            return response()->json([
                'result' => true,
                'message' => "Usuario actualizado con ??xito"
            ], 201);
        } catch (\Exception $exception) {

            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    //user deactivate function
    public function userDeactivate(UserDeactivateRequest $req)
    {
        try {
            //validation orderwork 
            $numOrders = orderWork::where('id_operador_responsable', $req->id_dato_usuario)
                ->whereNotIn('id_cat_estatus_ot', [4, 6])
                ->count();


            if ($numOrders > 0) {
                return response()->json([
                    'result' => false,
                    'message' => "El usuario no puede ser desactivado, aun tiene ordenes de entrega sin terminar"
                ], 201);
            }


            //update 
            User::where('id_dato_usuario', $req->id_dato_usuario)
                ->update(
                    [
                        'id_cat_estatus' => $req->id_cat_estatus,
                        'id_usuario_elimina' => auth()->user()->id_usuario,
                        'fecha_eliminacion' => Carbon::now()->format('Y-m-d H:i:s')
                    ]
                );

            return response()->json([
                'result' => true,
                'message' => "Usuario desactivado con ??xito"
            ], 201);
        } catch (\Exception $exception) {

            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //user update permission 
    // public function updateUserPermissions(UserPermissionRequest $req)
    // {
    //     try {
    //         User::where('id_dato_usuario', $req->id_dato_usuario)
    //             ->update(
    //                 [
    //                     'id_cat_perfil' => $req->id_cat_perfil,
    //                     'id_usuario_modifica' => auth()->user()->id_usuario,
    //                     'fecha_modificacion' => Carbon::now()->format('Y-m-d H:i:s')
    //                 ]
    //             );

    //         return response()->json([
    //             'result' => true,
    //             'message' => "Usuario editado con exito"
    //         ], 201);
    //     } catch (\Exception $exception) {

    //         //internal server error reponse 
    //         return response()->json([
    //             'result' => false,
    //             'message' => $exception->getMessage()
    //         ], 500);
    //     }
    // }



}
