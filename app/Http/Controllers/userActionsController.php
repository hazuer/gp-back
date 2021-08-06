<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\userData;
use App\catCustomers;
use App\catPlants;
use App\catProfiles;
use App\catStatus;
use Illuminate\Support\Str;

class userActionsController extends Controller
{

    public function dataUsersList(Request $req)
    {
        try {

            $listCustomers = catCustomers::select('id_cat_cliente', 'nombre_cliente')->get();
            $listPlants = catPlants::select('id_cat_planta', 'nombre_planta')->get();
            $listProfiles = catProfiles::select('id_cat_perfil', 'perfil')->get();
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
                    'datos_usuario.nombre',
                    'datos_usuario.apellido_paterno',
                    'datos_usuario.apellido_materno',
                    'cat_estatus.estatus',
                    'cat_perfil.perfil',
                    'cat_planta.nombre_planta',
                    'cat_cliente.nombre_cliente'
                )
                ->where('datos_usuario.id_dato_usuario', '!=', auth()->user()->id_dato_usuario);


            //if search contain name
            if ($req->has('nombre')) {
                $query->orWhereRaw("datos_usuario.nombre  LIKE '%" . $req->nombre . "%'");
            }
            //if search contain last name
            if ($req->has('apellido_paterno')) {
                $query->orWhereRaw("datos_usuario.apellido_paterno  LIKE '%" . $req->apellido_paterno . "%'");
            }
            //if search contain secondary last name
            if ($req->has('apellido_materno')) {
                $query->orWhereRaw("datos_usuario.apellido_materno  LIKE '%" . $req->pellido_materno . "%'");
            }
            //if search contain status
            if ($req->has('id_cat_estatus')) {
                $query->orWhere('usuario.id_cat_estatus', $req->id_cat_status);
            }
            //if search contain profile type

            if ($req->has('id_cat_perfil')) {
                $query->orWhere('usuario.id_cat_perfil', $req->id_cat_perfil);
            }

            //valid  type perfil
            switch (auth()->user()->id_cat_perfil) {
                case 1:
                    //if search contain plant
                    if ($req->has('id_cat_planta')) {
                        $query->orWhere('datos_usuario.id_cat_estatus', $req->id_cat_planta);
                    }
                    //if serach contain cliente
                    if ($req->has('id_cat_cliente')) {
                        $query->orWhere('usuario.id_cat_cliente', $req->id_cat_cliente);
                    }
                    break;
                case 4:
                    //if user is supervisor just can see your own plants and customers
                    $query->Where('usuario.id_cat_planta', auth()->user()->id_cat_planta)
                        ->where('usuario.id_cat_cliente', auth()->user()->id_cat_cliente);
                    break;
                default:
            }

            //method sort
            $direction  = "ASC";
            //if request has orderBy 
            $sortField = $req->has('ordenarPor') && !empty($req->ordenarPor) ? $req->ordenarPor : 'id_cat_estatus';

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
                case 'id_cat_estatus':
                    $sortField = "usuario.id_cat_estatus";
                    break;
                case 'id_cat_perfil':
                    $sortField = "usuario.id_cat_perfil";
                    break;
                case 'id_cat_planta':
                    $sortField = "usuario.id_cat_planta";
                    break;
                case 'id_cat_cliente':
                    $sortField = "usuario.id_cat_planta";
                    break;
            }

            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') ? intVal($req->pagina) : 1; //actual page
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
}
