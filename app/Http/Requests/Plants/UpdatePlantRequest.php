<?php

namespace App\Http\Requests\Plants;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantRequest extends FormRequest
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
            'nombre_planta' => 'required|max:255|unique:cat_planta,nombre_planta,' . request('id_cat_planta') . ',id_cat_planta',
            'id_cat_pais' => 'required'
        ];
    }
}
