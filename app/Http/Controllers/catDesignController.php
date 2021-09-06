<?php

namespace App\Http\Controllers;

use App\Models\orderWork;
use App\Models\catInks;
use App\Models\catDesign;
use App\Models\catDesignInks;

use App\Http\Requests\Desings\inkSearchRequest;
use App\Http\Requests\Desings\ActiveDeactiveDeleteDesignRequest;
use App\Http\Requests\Desings\RegisterDesignRequest;
use App\Http\Requests\Desings\importDesignRequest;
use App\Http\Requests\Desings\UpdateStatusInksDesignRequest;
use App\Http\Requests\Desings\UpdateDesignRequest;

use App\Imports\designImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;




class catDesignController extends Controller
{

    //function get list designs
    public function designsList(Request $req)
    {
        try {

            $query = catDesign::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_diseno.id_cat_estatus')
                ->leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'cat_diseno.id_cat_planta')
                ->leftJoin('diseno_tinta', 'diseno_tinta.id_cat_diseno', 'cat_diseno.id_cat_diseno')
                ->leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'diseno_tinta.id_cat_tinta')
                ->select(
                    'cat_diseno.id_cat_diseno',
                    'cat_diseno.nombre_diseno',
                    'cat_diseno.descripcion',
                    'cat_planta.nombre_planta',
                    'cat_estatus.estatus',
                    'cat_diseno.id_cat_estatus',
                    'cat_diseno.id_cat_planta'

                )->distinct();


            //if search has design name
            if ($req->has('nombre_diseno') && !is_null($req->nombre_diseno)) {
                $query->orWhereRaw("cat_diseno.nombre_diseno  LIKE '%" . $req->nombre_diseno . "%'");
            }
            //if search has design description
            if ($req->has('descripcion') && !is_null($req->descripcion)) {
                $query->orWhereRaw("cat_diseno.descripcion  LIKE '%" . $req->descripcion . "%'");
            }

            //if search has ink name
            if ($req->has('nombre_tinta') && !is_null($req->nombre_tinta)) {
                $query->orWhereRaw("cat_tinta.nombre_tinta  LIKE '%" . $req->nombre_tinta . "%'")
                    ->where('diseno_tinta.id_cat_estatus', 1);
            }

            //if search contain plan
            if ($req->has('id_cat_planta') && !is_null($req->id_cat_planta)) {
                $query->orWhere('cat_diseno.id_cat_planta', '=', $req->id_cat_planta);
            }
            //if search contain status
            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->Where('cat_diseno.id_cat_estatus', '=', $req->id_cat_estatus);
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
                case 'nombre_diseno':
                    $sortField = "cat_diseno.nombre_diseno";
                    break;
                case 'descripcion':
                    $sortField = "cat_diseno.descripcion";
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
            $designsTotal = $query->count('cat_diseno.nombre_diseno'); //total rows
            $designs = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result
            //final list
            $designsList = array();
            //construction desing
            foreach ($designs as $design) {
                $inks = array(); //inks to each designs
                //query to get inks 
                $inksList = catDesignInks::leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'diseno_tinta.id_cat_tinta')
                    ->where('diseno_tinta.id_cat_diseno', $design->id_cat_diseno)
                    ->where('diseno_tinta.id_cat_estatus', 1)
                    ->get();
                //each ink to desing
                foreach ($inksList as $ink) {
                    //add array inks 
                    array_push($inks, array(
                        'nombre_tinta' => $ink->nombre_tinta,
                        'codigo_cliente' => $ink->codigo_cliente,
                        'codigo_gp' => $ink->codigo_cliente,
                    ));
                }
                //add array final list
                array_push($designsList, array(
                    'id_cat_diseno' => $design->id_cat_diseno,
                    'nombre_diseno' => $design->nombre_diseno,
                    'descripcion' => $design->descripcion,
                    'tintas' => $inks, //all inks of design
                    'nombre_planta' => $design->nombre_planta,
                    'estatus' => $design->estatus,
                    'id_cat_estatus' => $design->id_cat_estatus,
                    'id_cat_planta' => $design->id_cat_planta
                ));
            }

            return response()->json([
                'result' => true,
                'designsTotal' =>  $designsTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($designsTotal / $perPage),
                'designsList' => $designsList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function Inks search
    public function inkSearch(inkSearchRequest $req)
    {

        try {
            $searchResult = catInks::where('id_cat_estatus', 1)
                ->select('id_cat_tinta', 'nombre_tinta', 'codigo_cliente', 'codigo_gp')
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('aditivo', 0)
                ->whereRaw("nombre_tinta LIKE '%" . $req->nombre_tinta . "%'")
                ->get();

            return response()->json([
                'result' => true,
                'searchResult' => $searchResult
            ], 201);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
    //function Design Register
    public function registerDesign(RegisterDesignRequest $req)
    {
        DB::beginTransaction();
        try {
            //valid if exists Design
            if (catDesign::where('nombre_diseno', $req->nombre_diseno)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe un diseño con este nombre asignada a esta planta"
                ], 400);
            }

            //variables user register. date and plant 
            $userId = auth()->user()->id_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');
            $plantId = $req->id_cat_planta;

            //insert Desing
            $newDesign = new  catDesign;
            $newDesign->nombre_diseno = $req->nombre_diseno;
            $newDesign->descripcion = $req->descripcion;
            $newDesign->id_cat_planta = $plantId;
            $newDesign->id_cat_estatus = 1;
            $newDesign->id_usuario_crea = $userId;
            $newDesign->fecha_creacion = $dateNow;
            $newDesign->save();

            foreach ($req->tintas as $id_cat_tinta) {
                //validation if exist this ink in the plant
                if (catInks::where('id_cat_tinta', $id_cat_tinta)
                    ->where('id_cat_planta', $plantId)
                    ->doesntExist()
                ) {
                    return response()->json([
                        'result' => true,
                        'message' => "Almenos una de las tintas seleccionadas no pertenece a esta planta, intenta nuevamente"
                    ], 400);
                }

                //insert desing Inks
                $newDesingInk = new  catDesignInks;
                $newDesingInk->id_cat_diseno = $newDesign->id_cat_diseno;
                $newDesingInk->id_cat_tinta = $id_cat_tinta;
                $newDesingInk->id_cat_estatus = 1;
                $newDesingInk->id_usuario_crea = $userId;
                $newDesingInk->fecha_creacion = $dateNow;
                $newDesingInk->save();
            }
            //commit
            DB::commit();
            //return response
            return response()->json([
                'result' => true,
                'message' => "Registro de diseño con éxito"
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

    //function update status Design, active, deactive and delete
    public function activeDeactiveDeleteDesign(ActiveDeactiveDeleteDesignRequest $req)
    {
        try {

            //variables user register, date
            $userId = auth()->user()->id_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');

            //validation if Design will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {

                $numOrders = orderWork::where('id_cat_diseno', $req->id_cat_diseno)
                    ->whereNotIn('id_cat_estatus_ot', [4, 6])
                    ->count();

                if ($numOrders > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "El diseño no puede ser desactivado o eliminado, aun tiene ordenes de entrega sin terminar"
                    ], 400);
                }
            }
            //update status Design
            $updateDesignStatus = catDesign::find($req->id_cat_diseno);
            $updateDesignStatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if machine will be delete
            if ($req->id_cat_estatus == 3) {
                $updateDesignStatus->id_usuario_elimina = $userId;
                $updateDesignStatus->fecha_eliminacion = $dateNow;
            } else {
                $updateDesignStatus->id_usuario_modifica = $userId;
                $updateDesignStatus->fecha_modificacion = $dateNow;
            }
            if ($updateDesignStatus->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de estatus con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar estatus"
                ], 400);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //function import Desing by CSV
    public function importDesignCSV(importDesignRequest $req)
    {

        $plant = $req->id_cat_planta;   //id cat planta
        $user = auth()->user()->id_usuario;  //user creator
        $dateNow = Carbon::now()->format('Y-m-d H:i:s');  //actual date
        $file =  $req->file('file')->store('temp'); //request file
        //import file
        $import = new designImport($plant, $user, $dateNow);
        $import->import($file);
        //return response
        return response()->json([
            'result' => true,
            'message' => "Registro de diseños con éxito"
        ], 201);
    }

    //get data Desing
    public function designData(Request $req)
    {
        try {
            //design
            $desing = [];
            //data design
            $desing['designData'] = catDesign::select(
                'cat_diseno.id_cat_diseno',
                'cat_diseno.nombre_diseno',
                'cat_diseno.descripcion',
                'cat_diseno.id_cat_planta',
                'cat_diseno.id_cat_estatus'
            )->where('id_cat_diseno', $req->id_cat_diseno)
                ->get();

            $inks = array(); //inks to each designs
            //query to get inks 
            $inksList = catDesignInks::leftJoin('cat_tinta', 'cat_tinta.id_cat_tinta', 'diseno_tinta.id_cat_tinta')
                ->leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'diseno_tinta.id_cat_estatus')
                ->where('diseno_tinta.id_cat_diseno', $req->id_cat_diseno)
                ->get();
            //each ink to desing
            foreach ($inksList as $ink) {
                //add array inks 
                array_push($inks, array(
                    'id_diseno_tinta' => $ink->id_diseno_tinta,
                    'nombre_tinta' => $ink->nombre_tinta,
                    'codigo_cliente' => $ink->codigo_cliente,
                    'codigo_gp' => $ink->codigo_cliente,
                    'estatus' => $ink->estatus
                ));
            }
            //add inks to desing
            $desing['inksDesign'] = $inks;

            return response()->json([
                'result' => true,
                'design' =>  $desing,
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //update status ink desing
    public function updateDesign(UpdateDesignRequest $req)
    {
        try {

            //variables user register, date
            $userId = auth()->user()->id_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');
            //check status design orders work
            $numOrders = orderWork::where('id_cat_diseno', $req->id_cat_diseno)
                ->whereNotIn('id_cat_estatus_ot', [4, 6])
                ->count();

            if ($numOrders > 0) {
                return response()->json([
                    'result' => false,
                    'message' => "El diseño no puede ser modificado, aun tiene ordenes de entrega sin terminar"
                ], 400);
            }

            //valid if exists Design
            if (catDesign::where('nombre_diseno', $req->nombre_diseno)
                ->where('id_cat_planta', $req->id_cat_planta)
                ->where('id_cat_diseno', '<>', $req->id_cat_diseno)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "ya existe un diseño con este nombre asignada a esta planta"
                ], 400);
            }

            //update  design
            $updateDesign = catDesign::find($req->id_cat_diseno);
            $updateDesign->id_cat_planta = $req->id_cat_planta;
            $updateDesign->nombre_diseno = $req->nombre_diseno;
            $updateDesign->descripcion = $req->descripcion;
            $updateDesign->id_usuario_modifica = $userId;
            $updateDesign->fecha_modificacion = $dateNow;
            if ($updateDesign->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de diseño con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar diseño"
                ], 400);
            }
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //update status ink desing
    public function updateStatusInksDesign(UpdateStatusInksDesignRequest $req)
    {
        try {

            //variables user register, date
            $userId = auth()->user()->id_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');

            //check status design orders work
            $numOrders = orderWork::where('id_cat_diseno', $req->id_cat_diseno)
                ->whereNotIn('id_cat_estatus_ot', [4, 6])
                ->count();

            if ($numOrders > 0) {
                return response()->json([
                    'result' => false,
                    'message' => "El diseño no puede ser modificado, aun tiene ordenes de entrega sin terminar"
                ], 400);
            }

            //update status inks designs
            $updateStatusInkDesign = catDesignInks::find($req->id_diseno_tinta);
            $updateStatusInkDesign->id_cat_estatus = $req->id_cat_estatus;
            //if status will be delete
            if ($req->id_cat_estatus == 3) {
                $updateStatusInkDesign->id_usuario_elimina = $userId;
                $updateStatusInkDesign->fecha_eliminacion = $dateNow;
            } else {
                $updateStatusInkDesign->id_usuario_modifica = $userId;
                $updateStatusInkDesign->fecha_modificacion = $dateNow;
            }

            if ($updateStatusInkDesign->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de estatus con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar estatus"
                ], 400);
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
