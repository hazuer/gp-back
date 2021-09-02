<?php

namespace App\Http\Requests\Countries;

use Illuminate\Foundation\Http\FormRequest;

class ActiveDeactiveDeleteCountryRequest extends FormRequest
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
            'id_cat_pais' => 'required',
            'id_cat_estatus' => 'required'
        ];
    }
}
