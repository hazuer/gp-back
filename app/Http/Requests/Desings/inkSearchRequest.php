<?php

namespace App\Http\Requests\Desings;

use Illuminate\Foundation\Http\FormRequest;

class inkSearchRequest extends FormRequest
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
            'nombre_tinta' => 'required',
            'id_cat_planta' => 'required'
        ];
    }
}
