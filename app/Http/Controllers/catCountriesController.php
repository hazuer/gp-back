<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\catCountries;
use App\Models\orderWork;

use App\Http\Requests\Countries\RegisterCountryRequest;
use App\Http\Requests\Countries\UpdateCountryRequest;
use App\Http\Requests\Countries\ActiveDeactiveDeleteCountryRequest;

use Illuminate\Support\Str;
use Carbon\Carbon;

class catCountriesController extends Controller
{

    //function get contries list
    public function countryList(Request $req)
    {

        try {

            $query = catCountries::leftJoin('cat_estatus', 'cat_estatus.id_cat_estatus', 'cat_pais.id_cat_estatus')
                ->select(
                    'cat_pais.id_cat_pais',
                    'cat_pais.nombre_pais',
                    'cat_estatus.estatus',
                    'cat_pais.id_cat_estatus'
                );

            //if search has nombre
            if ($req->has('nombre_pais') && !is_null($req->nombre_pais)) {
                $query->orWhereRaw("cat_pais.nombre_pais  LIKE '%" . $req->nombre_pais . "%'");
            }

            if ($req->has('id_cat_estatus') && !is_null($req->id_cat_estatus)) {
                $query->where("cat_pais.id_cat_estatus", $req->id_cat_estatus);
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
                case 'nombre_pais':
                    $sortField = "cat_pais.nombre_pais";
                    break;
                case 'estatus':
                    $sortField = "cat_estatus.estatus";
                    break;
            }

            //order list
            $query->orderBy($sortField, $direction);

            $perPage = $req->has('porPagina') && !is_null($req->porPagina)  ? intVal($req->porPagina) : 10; //num result per page
            $actualPage = $req->has('pagina') && !is_null($req->pagina) ? intVal($req->pagina) : 1; //actual page
            $countriesTotal = $query->count(); //total rows
            $countriesList = $query->offset(($actualPage - 1) * $perPage)->limit($perPage)->get(); //result


            return response()->json([
                'result' => true,
                'countriesTotal' =>  $countriesTotal,
                'actualPage' =>  $actualPage,
                'lastPage' => ceil($countriesTotal / $perPage),
                'countriesList' => $countriesList
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    //function register new country 
    public function registerCountry(RegisterCountryRequest $req)
    {
        try {

            //valid if name exists
            if (catCountries::where('nombre_pais', $req->nombre_pais)->exists()) {
                return response()->json([
                    'result' => false,
                    'message' => "El nombre de pais ya existe"
                ], 422);
            }

            $newCountry = new catCountries;
            $newCountry->nombre_pais = $req->nombre_pais;
            $newCountry->id_cat_estatus = 1;
            $newCountry->id_usuario_crea = auth()->user()->id_usuario;
            $newCountry->fecha_creacion = Carbon::now()->format('Y-m-d H:i:s');

            if ($newCountry->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro de pais con ??xito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al registrar pais"
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

    //function update country
    public function updateCountry(UpdateCountryRequest $req)
    {
        try {

            //valid if name exists
            if (catCountries::where('nombre_pais', $req->nombre_pais)
                ->where('id_cat_pais', '<>', $req->id_cat_pais)
                ->exists()
            ) {
                return response()->json([
                    'result' => false,
                    'message' => "El nombre de pa??s ya existe"
                ], 422);
            }
            //update country
            $updateCountry = catCountries::find($req->id_cat_pais);
            $updateCountry->nombre_pais = $req->nombre_pais;
            $updateCountry->id_usuario_modifica = auth()->user()->id_usuario;
            $updateCountry->fecha_modificacion = Carbon::now()->format('Y-m-d H:i:s');

            if ($updateCountry->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de pais con ??xito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error al actualizar pais"
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



    //function update status country, active, deactive and delete
    public function activeDeactiveDeleteCountry(ActiveDeactiveDeleteCountryRequest $req)
    {

        try {
            //variables user register, date
            $userId = auth()->user()->id_usuario;
            $dateNow = Carbon::now()->format('Y-m-d H:i:s');


            //validation if country will be delete or deactive 
            if ($req->id_cat_estatus == 2 || $req->id_cat_estatus == 3) {

                $numOrders = orderWork::leftJoin('cat_planta', 'cat_planta.id_cat_planta', 'orden_trabajo.id_cat_planta')
                    ->leftJoin('cat_pais', 'cat_pais.id_cat_pais', 'cat_planta.id_cat_pais')
                    ->where('cat_planta.id_cat_pais', $req->id_cat_pais)
                    ->whereNotIn('orden_trabajo.id_cat_estatus_ot', [4, 6])
                    ->count();

                if ($numOrders > 0) {
                    return response()->json([
                        'result' => false,
                        'message' => "El Pais no puede ser desactivado o eliminado, aun tiene ordenes de entrega sin terminar"
                    ], 201);
                }
            }
            //update status country
            $updateCountrystatus = catCountries::find($req->id_cat_pais);
            $updateCountrystatus->id_cat_estatus = $req->id_cat_estatus;
            //validation if country will be delete
            if ($req->id_cat_estatus == 3) {
                $updateCountrystatus->id_usuario_elimina = $userId;
                $updateCountrystatus->fecha_eliminacion = $dateNow;
            } else {
                $updateCountrystatus->id_usuario_modifica = $userId;
                $updateCountrystatus->fecha_modificacion = $dateNow;
            }

            if ($updateCountrystatus->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Actualizacion de estatus con ??xito"
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
