<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\catPlants;
use App\orderWork;
use App\Http\Requests\RegisterPlantRequest;
use App\Http\Requests\UpdatePlantRequest;
use App\Http\Requests\ActiveDeactiveDeletePlantRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;

class catPlantsController extends Controller
{


    //function  plants list
    public function plantsList(Request $req)
    {
        try {

            $query = catPlants::leftJoin('cat_pais', 'cat_pais.id_cat_pais', 'cat_planta.id_cat_pais')
                ->leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_planta.id_cat_estatus')
                ->select(
                    'cat_planta.id_cat_planta',
                    'cat_planta.nombre_planta',
                    'cat_pais.nombre_pais',
                    'cat_estatus.estatus'
                );

            //if search contain plant name
            if ($req->has('nombre_planta') && !is_null($req->nombre_planta)) {
                $query->orWhereRaw("cat_planta.nombre_planta  LIKE '%" . $req->nombre_planta . "%'");
            }
            //if search contain country
            if ($req->has('id_cat_pais') && !is_null($req->id_cat_pais)) {
                $query->orWhere('cat_planta.id_cat_pais', '=', $req->id_cat_pais);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->orWhere('cat_planta.id_cat_estatus', '=', $req->id_cat_estatus);
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
                case 'nombre_planta':
                    $sortField = "cat_planta.nombre_planta";
                    break;
                case 'id_cat_pais':
                    $sortField = "cat_planta.id_cat_pais";
                    break;
                case 'id_cat_estatus':
                    $sortField = "cat_planta.id_cat_estatus";
                    break;
            }

            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $plantsTotal = $query->count(); //total rows
            $plantsList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'plantsTotal' =>  $plantsTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($plantsTotal / $perPage),
                'plantsList' => $plantsList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function regiter plant
    public function registerPlant(RegisterPlantRequest $req)
    {
        try {
            $newPlant = new  catPlants;
            $newPlant->nombre_planta = $req->nombre_planta;
            $newPlant->id_cat_pais = $req->id_cat_pais;
            $newPlant->id_cat_estatus = 1;
            $newPlant->id_usuario_crea = auth()->user()->id_dato_usuario;
            $newPlant->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newPlant->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de planta con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar planta"
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

    //function update plant
    public function updatePlant(UpdatePlantRequest $req)
    {
        try {

            $updatePlant = catPlants::find($req->id_cat_planta);
            $updatePlant->nombre_planta = $req->nombre_planta;
            $updatePlant->id_cat_pais = $req->id_cat_pais;
            $updatePlant->id_usuario_modifica = auth()->user()->id_dato_usuario;
            $updatePlant->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($updatePlant->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de planta con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar planta"
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

    //function update status plant, active, deactive and delete
    public function activeDeactiveDeletePlant(ActiveDeactiveDeletePlantRequest $req)
    {

        try {

            //validation if plant will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {

                $numOrders = orderWork::where('id_cat_planta', $req->id_cat_planta)
                    ->whereIN('id_cat_estatus_ot', [1, 2, 3, 5])
                    ->count();

                if ($numOrders > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "La planta no puede ser desactivada o eliminada, aun tiene ordenes de trabajo sin terminar"
                    ], 201);
                }
            }
            //update status plant
            $updatePlantstatus = catPlants::find($req->id_cat_planta);
            $updatePlantstatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if plant will be delete
            if ($req->id_cat_estatus == 3) {
                $updatePlantstatus->id_usuario_elimina = auth()->user()->id_dato_usuario;
                $updatePlantstatus->fecha_eliminacion = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $updatePlantstatus->id_usuario_modifica = auth()->user()->id_dato_usuario;
                $updatePlantstatus->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            }

            if ($updatePlantstatus->save()) {
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
