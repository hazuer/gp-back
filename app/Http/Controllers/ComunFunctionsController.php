<?php

namespace App\Http\Controllers;

use App\Models\catPlants;
use App\Models\catStatus;
use App\Models\catCountries;
use App\Models\userLogs;
use Carbon\Carbon;

class ComunFunctionsController extends Controller
{
    //function geneeate random password
    public function generatePassword()
    {
        ///get string
        $uppers = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $lowers = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = "0123456789";
        $specialChars = '!#$%&/()=?@';
        //get string length
        $numberCount = mb_strlen($numbers);
        $upperCount = mb_strlen($uppers);
        $specialCharsCount = mb_strlen($specialChars);
        //get one random string char
        $upper  =  mb_substr($uppers, Rand(0, $upperCount - 1), 1);
        $number =  mb_substr($numbers, Rand(0, $numberCount - 1), 1);
        $special = mb_substr($specialChars, Rand(0, $specialCharsCount - 1), 1);
        $count  =  mb_strlen($lowers);
        //get one random string four
        for ($i = 0, $result = ''; $i < 5; $i++) {
            $result .= mb_substr($lowers, Rand(0, $count - 1), 1);
        }
        //random positions
        $password = str_shuffle($upper . $result . $number . $special);
        //retunr password
        return $password;
    }

    //function get countries and status
    public function countriesStatusList()
    {
        try {
            $listCountries = catCountries::select('id_cat_pais', 'nombre_pais')
                ->where('id_cat_estatus', 1)
                ->get();
            $listStatus = catStatus::all();

            return response()->json([
                'result' => true,
                'listCountries' => $listCountries,
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

    //get plants and status
    public function plantsStatusList()
    {
        try {
            $listPlants = catPlants::select('id_cat_planta', 'nombre_planta')
                ->where('id_cat_estatus', 1)
                ->get();
            $listStatus = catStatus::all();

            return response()->json([
                'result' => true,
                'listPlants' => $listPlants,
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


    public function registerLogs(
        $id_usuario,
        $tabla,
        $id_tabla,
        $actividad,
        $id_cat_planta
    ) {
        $newLog = new userLogs;
        $newLog->fecha = Carbon::now()->format('Y-m-d H:i:s');
        $newLog->id_usuario = $id_usuario;
        $newLog->tabla = $tabla;
        $newLog->id_tabla = $id_tabla;
        $newLog->actividad = $actividad;
        $newLog->id_cat_planta = $id_cat_planta;
        $newLog->save();
    }
}
