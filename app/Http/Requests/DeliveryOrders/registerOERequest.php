<?php

namespace App\Http\Requests\DeliveryOrders;

use Illuminate\Foundation\Http\FormRequest;

class registerOERequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if user is admin 
        if (auth()->user()->id_cat_perfil == 1 || auth()->user()->id_cat_perfil == 2 || auth()->user()->id_cat_perfil == 4) {
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
            'orden_trabajo_of' => 'required',
            'id_cat_maquina' => 'required',
            'id_cat_diseno' => 'required',
            'cantidad_programado' => 'nullable',
            'peso_total' => 'required',
            'id_cat_turno' => 'nullable',
            'linea' => 'required',
            'fecha_cierre_orden' => 'nullable',
            'tintas.*' => 'required',
        ];
    }
}
