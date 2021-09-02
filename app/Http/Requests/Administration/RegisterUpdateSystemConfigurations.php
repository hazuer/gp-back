<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUpdateSystemConfigurations extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if user is admin 
        if (auth()->user()->id_cat_perfil == 1) {
            return true;
        } else {

            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id_cat_planta' => 'required',
            'campo_lote'  => 'required',
            'campo_cantidad_programada' => 'required',
            'utiliza_tara'  => 'required',
            'campo_linea'  => 'required',
            'requiere_turno'  => 'required',
            'variacion_maxima'  => 'required',
            'porcentaje_variacion_aceptado'  => 'required',
            'utiliza_ph'  => 'nullable',
            'mide_viscosidad'  => 'nullable',
            'utiliza_filtro'  => 'nullable',
        ];
    }
}
