<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\orderWork;
use App\Models\catInks;

use App\Http\Requests\Inks\RegisterInkRequest;
use App\Http\Requests\Inks\UpdateInkRequest;
use App\Http\Requests\Inks\ActiveDeactiveDeleteInkRequest;
use App\Http\Requests\Inks\importInkCsvRequest;

use App\Imports\inkImport;
use Illuminate\Support\Str;
use Carbon\Carbon;

class catInksControllers extends Controller
{

    //function list Inks
    public function inksList(Request $req)
    {
        try {

            $query = catInks::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_tinta.id_cat_estatus')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'cat_tinta.id_cat_planta')
                ->select(
                    'cat_tinta.id_cat_tinta',
                    'cat_tinta.nombre_tinta',
                    'cat_tinta.codigo_cliente',
                    'cat_tinta.codigo_gp',
                    'cat_tinta.aditivo',
                    'cat_planta.nombre_planta',
                    'cat_estatus.estatus',
                    'cat_tinta.id_cat_estatus',
                    'cat_tinta.id_cat_planta'

                );
            //if search has ink name
            if ($req->has('nombre_tinta') && !is_null($req->nombre_tinta)) {
                $query->orWhereRaw("cat_tinta.nombre_tinta  LIKE '%" . $req->nombre_tinta . "%'");
            }
            //if search has ink customer code
            if ($req->has('codigo_cliente') && !is_null($req->codigo_cliente)) {
                $query->orWhereRaw("cat_tinta.codigo_cliente  LIKE '%" . $req->codigo_cliente . "%'");
            }

            //if search has ink gp code
            if ($req->has('codigo_gp') && !is_null($req->codigo_gp)) {
                $query->orWhereRaw("cat_tinta.codigo_gp  LIKE '%" . $req->codigo_gp . "%'");
            }

            //if search contain country
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_tinta.id_cat_planta', '=', $req->id_cat_planta);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->where('cat_tinta.id_cat_estatus', '=', $req->id_cat_estatus);
            }

            //if search aditivo status
            if ($req->has('aditivo') && !is_null($req->aditivo)) {
                $query->where('cat_tinta.aditivo', '=', $req->aditivo);
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
                case 'nombre_tinta':
                    $sortField = "cat_tinta.nombre_tinta";
                    break;
                case 'codigo_cliente':
                    $sortField = "cat_tinta.codigo_cliente";
                    break;
                case 'codigo_gp':
                    $sortField = "cat_tinta.codigo_gp";
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
            $inksTotal = $query->count(); //total rows
            $inkList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result

            return response()->json([
                'result' => true,
                'inksTotal' =>  $inksTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($inksTotal / $perPage),
                'inkList' => $inkList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function regiter Ink
    public function registerInk(RegisterInkRequest $req)
    {
        try {
            //valid if ink exists
            if (catInks::where('codigo_gp', $req->codigo_gp)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe un Codigo GP con esta descripcion asignado a esta planta."
                ], 401);
            }

            //valid aditivo
            $aditivo = $req->has('aditivo') && $req->aditivo == true ? 1 : 0;
            //register ink
            $newInk = new  catInks;
            $newInk->nombre_tinta = $req->nombre_tinta;
            $newInk->codigo_cliente = $req->codigo_cliente;
            $newInk->codigo_gp = $req->codigo_gp;
            $newInk->id_cat_planta = $req->id_cat_planta;
            $newInk->id_cat_estatus = 1;
            $newInk->aditivo = $aditivo;
            $newInk->id_usuario_crea = auth()->user()->id_dato_usuario;
            $newInk->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newInk->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de tinta con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar tinta"
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

    //function update ink
    public function updateInk(UpdateInkRequest $req)
    {
        try {
            //valid if ink exists
            if (catInks::where('codigo_gp', $req->codigo_gp)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('id_cat_tinta', '<>', $req->id_cat_tinta)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe un Codigo GP con esta descripcion asignado a esta planta."
                ], 401);
            }
            //valid aditivo
            $aditivo = $req->has('aditivo') && $req->aditivo == true ? 1 : 0;
            //update ink
            $updateInk = catInks::find($req->id_cat_tinta);
            $updateInk->nombre_tinta = $req->nombre_tinta;
            $updateInk->codigo_cliente = $req->codigo_cliente;
            $updateInk->codigo_gp = $req->codigo_gp;
            $updateInk->aditivo = $aditivo;
            $updateInk->id_usuario_modifica = auth()->user()->id_dato_usuario;
            $updateInk->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($updateInk->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de tinta con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar tinta"
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
    //function active, deactive and delete ink
    public function activeDeactiveDeleteInk(ActiveDeactiveDeleteInkRequest $req)
    {
        try {

            //variables user register, date
            $userId = auth()->user()->id_dato_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');
            //validation if ink will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {

                //count num design orders that doesn't closed
                $numOrderDesign = orderWork::leftJoin('diseno_tinta', 'diseno_tinta.id_cat_diseno', 'orden_trabajo.id_cat_diseno')
                    ->where('diseno_tinta.id_cat_tinta', $req->id_cat_tinta)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                //count num orders deliveries that doesn't closed
                $numOrderDeliveries = orderWork::leftJoin('ot_detalle_tinta', 'ot_detalle_tinta.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->where('ot_detalle_tinta.id_cat_tinta', $req->id_cat_tinta)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                //count num return orders that doesn't closed
                $numOrdersReturn = orderWork::leftJoin('devolucion', 'devolucion.id_orden_trabajo', 'orden_trabajo.id_orden_trabajo')
                    ->leftJoin('devolucion_detalle_tinta', 'devolucion_detalle_tinta.id_devolucion', 'devolucion.id_devolucion')
                    ->where('devolucion_detalle_tinta.id_cat_tinta', $req->id_cat_tinta)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                if ($numOrderDesign > 0  || $numOrdersReturn > 0 || $numOrderDeliveries > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "La Tinta no puede ser desactivada o eliminada, aun tiene ordenes de trabajo sin terminar"
                    ], 201);
                }
            }

            //update status ink
            $updateInktatus = catInks::find($req->id_cat_tinta);
            $updateInktatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if ink will be delete
            if ($req->id_cat_estatus == 3) {
                $updateInktatus->id_usuario_elimina =  $userId;
                $updateInktatus->fecha_eliminacion = $dateNow;
            } else {
                $updateInktatus->id_usuario_modifica =  $userId;
                $updateInktatus->fecha_modificacion = $dateNow;
            }

            if ($updateInktatus->save()) {
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

    public function importInkCSV(importInkCsvRequest $req)
    {
        $plant = $req->id_cat_planta;   //id cat planta
        $user = auth()->user()->id_dato_usuario;  //user creator
        $dateNow = Carbon::now()->format('Y-m-d H:i:s');  //actual date
        $file = $req->file('archivo_tintas_importar'); //request file
        //import file
        $import = new inkImport($plant, $user, $dateNow);
        $import->import($file);
        //return response
        return response()->json([
            'result' => true,
            'message' => "Registro de tintas con éxito"
        ], 201);
    }
}
