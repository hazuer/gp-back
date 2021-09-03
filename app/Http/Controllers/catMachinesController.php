<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\orderWork;
use App\Models\catMachines;

use App\Http\Requests\Machines\RegisterMachineRequest;
use App\Http\Requests\Machines\UpdateMachineRequest;
use App\Http\Requests\Machines\ActiveDeactiveDeleteMachineRequest;

use Illuminate\Support\Str;
use Carbon\Carbon;

class catMachinesController extends Controller
{
    //function get machines list
    public function machinesList(Request $req)
    {

        try {

            $query = catMachines::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_maquina.id_cat_estatus')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'cat_maquina.id_cat_planta')
                ->select(
                    'cat_maquina.id_cat_maquina',
                    'cat_maquina.nombre_maquina',
                    'cat_maquina.modelo',
                    'cat_planta.nombre_planta',
                    'cat_estatus.estatus',
                    'cat_maquina.id_cat_estatus',
                    'cat_maquina.id_cat_planta'
                );

            //if search contain machine name
            if ($req->has('nombre_maquina') && !is_null($req->nombre_maquina)) {
                $query->orWhereRaw("cat_maquina.nombre_maquina  LIKE '%" . $req->nombre_maquina . "%'");
            }

            //if search contain model
            if ($req->has('modelo') && !is_null($req->modelo)) {
                $query->orWhereRaw("cat_maquina.modelo  LIKE '%" . $req->modelo . "%'");
            }

            //if search contain plant
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_maquina.id_cat_planta', '=', $req->id_cat_planta);
            }

            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->Where('cat_maquina.id_cat_estatus', '=', $req->id_cat_estatus);
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
                case 'nombre_maquina':
                    $sortField = "cat_maquina.nombre_maquina";
                    break;
                case 'modelo':
                    $sortField = "cat_maquina.modelo";
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
            $machinesTotal = $query->count(); //total rows
            $machinesList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'machinesTotal' =>  $machinesTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($machinesTotal / $perPage),
                'machinesList' => $machinesList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function register machine
    public function registerMachine(RegisterMachineRequest $req)
    {
        try {
            //valid if existe machine 
            if (catMachines::where('nombre_maquina', $req->nombre_maquina)
                ->where('id_cat_planta', $req->id_cat_planta)->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una maquina con este nombre asignada a esta planta"
                ], 401);
            }

            $newMachine = new  catMachines;
            $newMachine->nombre_maquina = $req->nombre_maquina;
            $newMachine->modelo = $req->modelo;
            $newMachine->id_cat_planta = $req->id_cat_planta;
            $newMachine->id_cat_estatus = 1;
            $newMachine->id_usuario_crea = auth()->user()->id_usuario;
            $newMachine->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($newMachine->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de maquina con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar maquina"
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

    //function register machine
    public function updateMachine(UpdateMachineRequest $req)
    {
        try {

            //valid if existe machine 
            if (catMachines::where('nombre_maquina', $req->nombre_maquina)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('id_cat_maquina', '<>', $req->id_cat_maquina)->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe una maquina con este nombre asignada a esta planta"
                ], 401);
            }

            $updateMachine = catMachines::find($req->id_cat_maquina);
            $updateMachine->nombre_maquina = $req->nombre_maquina;
            $updateMachine->modelo = $req->modelo;
            $updateMachine->id_cat_planta = $req->id_cat_planta;
            $updateMachine->id_usuario_modifica = auth()->user()->id_usuario;
            $updateMachine->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            if ($updateMachine->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de maquina con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar maquina"
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


    //function update status machine, active, deactive and delete
    public function activeDeactiveDeleteMachine(ActiveDeactiveDeleteMachineRequest $req)
    {
        try {

            //validation if machine will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {

                $numOrders = orderWork::where('id_cat_maquina', $req->id_cat_maquina)
                    ->whereNotIn('id_cat_estatus_ot', [4, 6])
                    ->count();

                if ($numOrders > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "La maquina no puede ser desactivada o eliminada, aun tiene ordenes de entrega sin terminar"
                    ], 201);
                }
            }
            //update status machine
            $updateMachineStatus = catMachines::find($req->id_cat_maquina);
            $updateMachineStatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if machine will be delete
            if ($req->id_cat_estatus == 3) {
                $updateMachineStatus->id_usuario_elimina = auth()->user()->id_usuario;
                $updateMachineStatus->fecha_eliminacion = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $updateMachineStatus->id_usuario_modifica = auth()->user()->id_usuario;
                $updateMachineStatus->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');
            }
            if ($updateMachineStatus->save()) {
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
