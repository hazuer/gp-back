<?php

namespace App\Http\Requests\DeliveryOrders;

use Illuminate\Foundation\Http\FormRequest;

class authorizeDifferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'correo' => 'required|email',
            'password' => 'required',
            'id_ot_detalle_tinta' => 'required'
        ];
    }
}
