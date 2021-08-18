<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\orderWork;
use App\catTaras;
use App\Http\Requests\RegisterTaraRequest;
use App\Http\Requests\UpdateTaraRequest;
use App\Http\Requests\ActiveDeactiveDeleteTaraRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;

class catTarasController extends Controller
{



    //function get taras list
    public function tarasList(Request $req)
    {

        try {

            $query = catTaras::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_tara.id_cat_estatus')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'cat_tara.id_cat_planta')
                ->select(
                    'cat_tara.id_cat_tara',
                    'cat_tara.nombre_tara',
                    'cat_tara.capacidad',
                    'cat_planta.nombre_planta',
                    'cat_estatus.estatus'
                );

            //if search has tara name
            if ($req->has('nombre_tara') && !is_null($req->nombre_tara)) {
                $query->orWhereRaw("cat_tara.nombre_tara  LIKE '%" . $req->nombre_tara . "%'");
            }

            //if search has capacidad 
            if ($req->has('capacidad') && !is_null($req->capacidad)) {
                $query->orWhere('cat_tara.capacidad', $req->capacidad);
            }

            //if search contain country
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_tara.id_cat_planta', '=', $req->id_cat_planta);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->orWhere('cat_tara.id_cat_estatus', '=', $req->id_cat_estatus);
            }

            //method sort
            $direction  = "ASC";
            //if request has orderBy 
            $sortField = $req->has('ordenarPor') && !is_null($req->ordenarPor) ? $req->ordenarPor : 'id_cat_estatus';

            if (Str::of($sortField)->startsWith('-')) {
                $direction  = "DESC";
                $sortField = Str::of($sortField)->substr(1);
            }
            switch ($sortField) {
                case 'nombre_tara':
                    $sortField = "cat_tara.nombre_tara";
                    break;
                case 'capacidad':
                    $sortField = "cat_tara.capacidad";
                    break;
                case 'id_cat_planta':
                    $sortField = "cat_tara.id_cat_planta";
                    break;
                case 'id_cat_estatus':
                    $sortField = "cat_tara.id_cat_estatus";
                    break;
            }
            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $tarasTotal = $query->count(); //total rows
            $tarassList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'tarasTotal' =>  $tarasTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($tarasTotal / $perPage),
                'tarassList' => $tarassList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function register tara
    public function registerTara(RegisterTaraRequest $req)
    {
        try {

            //valid if exists tara
            if (catTaras::where('nombre_tara', $req->nombre_tara)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una tara con este nombre asignada a esta planta"
                ], 401);
            }
            //insert tara
            $newTara = new  catTaras;
            $newTara->nombre_tara = $req->nombre_tara;
            $newTara->capacidad = $req->capacidad;
            $newTara->id_cat_planta = $req->id_cat_planta;
            $newTara->id_cat_estatus = 1;
            $newTara->id_usuario_crea = auth()->user()->id_dato_usuario;
            $newTara->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newTara->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de tara con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar tara"
                ], 401);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
    //function update tara
    public function updateTara(UpdateTaraRequest $req)
    {
        try {
            //valid if exists tara
            if (catTaras::where('nombre_tara', $req->nombre_tara)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('id_cat_tara', '<>', $req->id_cat_tara)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una tara con este nombre asignada a esta planta"
                ], 401);
            }

            //update tara
            $updateTara = catTaras::find($req->id_cat_tara);
            $updateTara->nombre_tara = $req->nombre_tara;
            $updateTara->capacidad = $req->capacidad;
            $updateTara->id_cat_planta = $req->id_cat_planta;
            $updateTara->id_usuario_modifica = auth()->user()->id_dato_usuario;
            $updateTara->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($updateTara->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de tara con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar tara"
                ], 401);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function active deactive and delete tara
    public function activeDeactiveDeleteTara(ActiveDeactiveDeleteTaraRequest $req)
    {
        try {
            //validation if tara will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {
                //count num deliveries orders that doesn't closed
                $numOrderDeliveries = orderWork::leftJoin('entrega', 'entrega.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->leftJoin('entrega_detalle_tinta', 'entrega_detalle_tinta.id_entrega', 'entrega.id_entrega')
                    ->where('entrega_detalle_tinta.id_cat_tara', $req->id_cat_tara)
                    ->whereIN('orden_trabajo.id_cat_estatus_ot', [1, 2, 3, 5])
                    ->count();
                //count num return orders that doesn't closed
                $numOrdersReturn = orderWork::leftJoin('devolucion', 'devolucion.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->leftJoin('devolucion_detalle_tinta', 'devolucion_detalle_tinta.id_devolucion', 'devolucion.id_devolucion')
                    ->where('devolucion_detalle_tinta.id_cat_tara', $req->id_cat_tara)
                    ->whereIN('orden_trabajo.id_cat_estatus_ot', [1, 2, 3, 5])
                    ->count();
                //count num ink detail orders that doesn't closed
                $numOrdersInkdetail = orderWork::leftJoin('ot_detalle_tinta', 'ot_detalle_tinta.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->where('ot_detalle_tinta.id_cat_tara', $req->id_cat_tara)
                    ->whereIN('orden_trabajo.id_cat_estatus_ot', [1, 2, 3, 5])
                    ->count();

                if ($numOrderDeliveries > 0 || $numOrdersReturn > 0  || $numOrdersInkdetail > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "La tara no puede ser desactivada o eliminada, aun tiene ordenes de trabajo sin terminar"
                    ], 201);
                }
            }
            //update status tara
            $updateTaraStatus = catTaras::find($req->id_cat_tara);
            $updateTaraStatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if tara will be delete
            if ($req->id_cat_estatus == 3) {
                $updateTaraStatus->id_usuario_elimina = auth()->user()->id_dato_usuario;
                $updateTaraStatus->fecha_eliminacion = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $updateTaraStatus->id_usuario_modifica = auth()->user()->id_dato_usuario;
                $updateTaraStatus->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            }

            if ($updateTaraStatus->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de estatus con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar estatus"
                ], 401);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}
