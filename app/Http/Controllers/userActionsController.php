<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\userData;

class userActionsController extends Controller
{

    public function userList(Request $req)
    {

        try {

            $typeUser = '';
            $query = userData::leftJoin('usuario', 'usuario.id_dato_usuario', '=', 'datos_usuario.id_dato_usuario')
                ->leftJoin('cat_cliente', 'cat_cliente.id_cat_cliente', '=', 'usuario.id_cat_cliente')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', '=', 'usuario.id_cat_planta')
                ->leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', '=', 'usuario.id_cat_estatus')
                ->leftJoin('cat_perfil', 'usuario.id_cat_perfil', '=', 'usuario.id_cat_perfil')

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

            if ($req->has('nombre')) {
                $query->orWhereRaw("datos_usuario.nombre  LIKE '%" . $req->nombre . "%'");
            }
            if ($req->has('apellido_paterno')) {
                $query->orWhereRaw("datos_usuario.apellido_paterno  LIKE '%" . $req->apellido_paterno . "%'");
            }
            if ($req->has('apellido_materno')) {
                $query->orWhereRaw("datos_usuario.apellido_materno  LIKE '%" . $req->pellido_materno . "%'");
            }

            if ($req->has('id_cat_estatus')) {
                $query->orWhere('usuario.id_cat_estatus', $req->id_cat_status);
            }

            if ($req->has('id_cat_perfil')) {
                $query->orWhere('usuario.id_cat_perfil', $req->id_cat_perfil);
            }

            switch (auth()->user()->id_cat_perfil) {
                case 1:
                    $typeUser = 'administrador';

                    if ($req->has('id_cat_planta')) {
                        $query->orWhere('datos_usuario.id_cat_estatus', $req->id_cat_planta);
                    }
                    if ($req->has('id_cat_cliente')) {
                        $query->orWhere('usuario.id_cat_cliente', $req->id_cat_cliente);
                    }
                    break;
                case 4:
                    $typeUser = 'supervidor';
                    $query->Where('usuario.id_cat_planta', auth()->user()->id_cat_planta)
                        ->where('usuario.id_cat_cliente', auth()->user()->id_cat_cliente);
                    break;
                default:
            }


            $perPage = $req->has('porPagina') ? intVal($req->porPagina) : 10; //num per page
            $actualPage = $req->has('pagina') ? intVal($req->pagina) : 1; //page actual
            $usersTotal = $query->count(); //total rows
            $usersList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'typeUserRequest' => $typeUser,
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
