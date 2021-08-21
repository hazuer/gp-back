<?php

namespace App\Http\Controllers;

use App\catReasons;
use App\orderWork;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterReasonRequest;
use App\Http\Requests\UpdateReasonRequest;
use App\Http\Requests\ActiveDeactiveDeleteReasonRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;

class catReasonsController extends Controller
{
    //function get reasons list
    public function reasonsList(Request $req)
    {

        try {

            $query = catReasons::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_razon.id_cat_estatus')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'cat_razon.id_cat_planta')
                ->select(
                    'cat_razon.id_cat_razon',
                    'cat_razon.razon',
                    'cat_planta.nombre_planta',
                    'cat_estatus.estatus'
                );

            //if search has reazon
            if ($req->has('razon') && !is_null($req->razon)) {
                $query->orWhereRaw("cat_razon.razon  LIKE '%" . $req->razon . "%'");
            }

            //if search contain country
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_razon.id_cat_planta', '=', $req->id_cat_planta);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->orWhere('cat_razon.id_cat_estatus', '=', $req->id_cat_estatus);
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
                case 'razon':
                    $sortField = "cat_razon.razon";
                    break;
                case 'nombre_planta':
                    $sortField = "cat_planta.nombre_planta";
                    break;
                case 'estatus':
                    $sortField = "cat_estatus.estatus";
                    break;
            }
            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $reasonsTotal = $query->count(); //total rows
            $reasonsList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'reasonsTotal' =>  $reasonsTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($reasonsTotal / $perPage),
                'reasonsList' => $reasonsList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    public function registerReason(RegisterReasonRequest $req)
    {
        try {

            //valid if existe razon
            if (catReasons::where('razon', $req->razon)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una razon con este nombre asignada a esta planta"
                ], 401);
            }
            //insert reazon
            $newReason = new  catReasons;
            $newReason->razon = $req->razon;
            $newReason->id_cat_planta = $req->id_cat_planta;
            $newReason->id_cat_estatus = 1;
            $newReason->id_usuario_crea = auth()->user()->id_dato_usuario;
            $newReason->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newReason->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de razon con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar razon"
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


    //function update reazon
    public function updateReason(UpdateReasonRequest $req)
    {
        try {
            //valid if existe razon 
            if (catReasons::where('razon', $req->razon)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('id_cat_razon', '<>', $req->id_cat_razon)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una razon con este nombre asignada a esta planta"
                ], 401);
            }
            //update razon
            $updateReason = catReasons::find($req->id_cat_razon);
            $updateReason->razon = $req->razon;
            $updateReason->id_cat_planta = $req->id_cat_planta;
            $updateReason->id_usuario_modifica = auth()->user()->id_dato_usuario;
            $updateReason->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($updateReason->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de razon con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar razon"
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

    //function active deactive and delete reazon
    public function activeDeactiveDeleteReazon(ActiveDeactiveDeleteReasonRequest $req)
    {
        try {
            //variables user register, date
            $userId = auth()->user()->id_dato_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');

            //validation if reazon will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {

                //count num orders deliveries that doesn't closed
                $numOrderDeliveries = orderWork::leftJoin('entrega', 'entrega.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->leftJoin('entrega_detalle_tinta', 'entrega_detalle_tinta.id_entrega', 'entrega.id_entrega')
                    ->where('entrega_detalle_tinta.id_cat_razon', $req->id_cat_razon)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                //count num orders return that doesn't closed
                $numOrdersReturn = orderWork::leftJoin('devolucion', 'devolucion.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->leftJoin('devolucion_detalle_tinta', 'devolucion_detalle_tinta.id_devolucion', 'devolucion.id_devolucion')
                    ->where('devolucion_detalle_tinta.id_cat_razon', $req->id_cat_razon)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();


                if ($numOrderDeliveries > 0 ||  $numOrdersReturn > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "La razon no puede ser desactivada o eliminada, aun tiene ordenes de trabajo sin terminar"
                    ], 201);
                }
            }
            //update status razon
            $updateRaesonstatus = catReasons::find($req->id_cat_razon);
            $updateRaesonstatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if razon will be delete
            if ($req->id_cat_estatus == 3) {
                $updateRaesonstatus->id_usuario_elimina =  $userId;
                $updateRaesonstatus->fecha_eliminacion = $dateNow;
            } else {
                $updateRaesonstatus->id_usuario_modifica =  $userId;
                $updateRaesonstatus->fecha_modificacion = $dateNow;
            }

            if ($updateRaesonstatus->save()) {
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
