<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'id_cat_cliente' => 'required',
            'nombre_cliente' => 'required|max:255|unique:cat_cliente,nombre_cliente,' . request('id_cat_cliente') . ',id_cat_cliente',
            'id_cat_planta' => 'required'
        ];
    }
}
