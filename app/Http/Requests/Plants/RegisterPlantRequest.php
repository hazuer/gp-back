<?php

namespace App\Http\Requests\Plants;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPlantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if user is admin or supervisor
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
            'nombre_planta' => 'required|max:255',
            'id_cat_pais' => 'required'
        ];
    }
}
