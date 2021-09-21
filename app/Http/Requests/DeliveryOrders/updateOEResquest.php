<?php

namespace App\Http\Requests\DeliveryOrders;

use Illuminate\Foundation\Http\FormRequest;

class updateOEResquest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if user is supervior or operator
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
            'id_orden_trabajo' => 'required'
        ];
    }
}
