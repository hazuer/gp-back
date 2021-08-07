<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (auth()->user()->id_cat_perfil == 1 || auth()->user()->id_cat_perfil == 4) {
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
            'id_dato_usuario' => 'required',
            'correo' => 'required|max:75|unique:usuario,correo,NULL,NULL,id_dato_usuario,' . request('id_dato_usario'),
            'id_cat_planta' => 'required',
            'id_cat_cliente' => 'required',
            'id_cat_perfil' => 'required',
            'id_cat_estatus' => 'required'
        ];
    }
}
