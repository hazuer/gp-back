<?php

namespace App\Http\Requests\DeliveryOrders;

use Illuminate\Foundation\Http\FormRequest;

class receiveOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if user is customer or supervisor customer
        if (auth()->user()->id_cat_perfil == 3 || auth()->user()->id_cat_perfil == 6) {
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
            'id_orden_trabajo' => 'required',
            'peso_total_recibido' => 'required',
            'id_orden_trabajo' => 'required',
            'id_orden_trabajo' => 'required',
            'tintas.*' => 'required',
        ];
    }
}
