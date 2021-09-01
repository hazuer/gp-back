<?php

namespace App\Http\Requests\Reasons;

use Illuminate\Foundation\Http\FormRequest;

class RegisterReasonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if user is admin or supervisor
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
            'razon' => 'required|max:255',
            'id_cat_planta' => 'required'
        ];
    }
}
