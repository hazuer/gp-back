<?php

namespace App\Imports;

use App\catInks;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class inkImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{

    use  Importable, SkipsErrors;

    public function  __construct($plant, $user, $dateNow)
    {
        $this->plant = $plant;
        $this->user = $user;
        $this->dateNow = $dateNow;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        //insert
        return new catInks([
            'nombre_tinta' => $row['nombre_tinta'],
            'codigo_sap' => $row['codigo_sap'],
            'codigo_gp' => $row['codigo_gp'],
            'id_cat_estatus'  => 1,
            'id_cat_planta'  => $this->plant,
            'id_usuario_crea'  => $this->user,
            'fecha_creacion'  => $this->dateNow,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nombre_tinta' => ['required', 'max:75'],
            '*.codigo_sap' => ['required', 'max:25'],
            '*.codigo_gp' => [
                'required', 'max:25',
                function ($attribute, $value, $onFailure) {
                    //valid if codigo gp exist 
                    if (catInks::where('codigo_gp', $value)
                        ->where('id_cat_planta', $this->plant)
                        ->exists()
                    ) {
                        $onFailure('El codigo_gp ' . $value . ' esta repetido en el documento o ya esta resgitrado para esta planta.');
                    }
                }
            ],
        ];
    }
}
