<?php

namespace App\Http\Requests\Inks;

use Illuminate\Foundation\Http\FormRequest;

class ActiveDeactiveDeleteInkRequest extends FormRequest
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
            'id_cat_tinta' => 'required',
            'id_cat_estatus' => 'required',
        ];
    }
}
