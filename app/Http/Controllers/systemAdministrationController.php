<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\systemConfigurations;

use App\Http\Requests\Administration\RegisterUpdateSystemConfigurations;

use Illuminate\Support\Str;
use Carbon\Carbon;


class systemAdministrationController extends Controller
{

    //get system params
    public function getSystemParams(Request $req)
    {
        try {
            $params = systemConfigurations::select(
                'id_cat_planta',
                'campo_lote',
                'campo_cantidad_programada',
                'utiliza_tara',
                'campo_linea',
                'requiere_turno',
                'variacion_maxima',
                'porcentaje_variacion_aceptado',
                'utiliza_ph',
                'mide_viscosidad',
                'utiliza_filtro'
            )
                ->where('id_cat_planta', $req->id_cat_planta)->first();

            return response()->json([
                'result' => true,
                'systemParams' => $params
            ], 200);
        } catch (\Exception $exception) {
            //internal server error reponse 
            return response()->json([
                'result' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    //create or update system paramsn
    public function registerUpdateSystemParams(RegisterUpdateSystemConfigurations $req)
    {
        try {

            $params = systemConfigurations::firstOrNew(['id_cat_planta' => $req->id_cat_planta]);
            $params->campo_lote = $req->campo_lote;
            $params->campo_cantidad_programada = $req->campo_cantidad_programada;
            $params->utiliza_tara = $req->utiliza_tara;
            $params->campo_linea = $req->campo_linea;
            $params->requiere_turno = $req->requiere_turno;
            $params->variacion_maxima = $req->variacion_maxima;
            $params->porcentaje_variacion_aceptado = $req->porcentaje_variacion_aceptado;
            $params->utiliza_ph = $req->utiliza_ph;
            $params->mide_viscosidad = $req->mide_viscosidad;
            $params->utiliza_filtro = $req->utiliza_filtro;
            //if register exists
            if ($params->exists) {
                $params->id_usuario_modifica = auth()->user()->id_usuario;
                $params->fecha_modificacion =  Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $params->id_cat_estatus = 1;
                $params->id_usuario_crea = auth()->user()->id_usuario;
                $params->fecha_creacion =  Carbon::now()->format('Y-m-d H:i:s');
            }
            if ($params->save()) {
                return response()->json([
                    'result' => true,
                    'message' => "Registro con éxito"
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => "Error con el registro"
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
