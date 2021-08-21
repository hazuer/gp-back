<?php

namespace App\Imports;

use App\catInks;
use App\catDesign;
use App\catDesignInks;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;

class designImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError
{

    use  Importable, SkipsErrors;

    //call variables 
    public function  __construct($plant, $user, $dateNow)
    {
        $this->plant = $plant;
        $this->user = $user;
        $this->dateNow = $dateNow;
    }


    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {


            $newDesign = new  catDesign;
            $newDesign->nombre_diseno = $row['nombre_diseno'];
            $newDesign->descripcion = $row['descripcion'];
            $newDesign->id_cat_planta = $this->plant;
            $newDesign->id_cat_estatus = 1;
            $newDesign->id_usuario_crea = $this->user;
            $newDesign->fecha_creacion = $this->dateNow;
            $newDesign->save();

            //explode codes gp
            $codigos_gp = explode(',', $row['codigos_gp']);
            //
            foreach ($codigos_gp as $codigo_gp) {
                $ink = catInks::where('codigo_gp', $codigo_gp)
                    ->where('id_cat_planta', $this->plant)->first();
                //insert desing Inks
                $newDesingInk = new  catDesignInks;
                $newDesingInk->id_cat_diseno = $newDesign->id_cat_diseno;
                $newDesingInk->id_cat_tinta =  $ink->id_cat_tinta;
                $newDesingInk->id_cat_estatus = 1;
                $newDesingInk->id_usuario_crea = $this->user;
                $newDesingInk->fecha_creacion = $this->dateNow;
                $newDesingInk->save();
            }
        }
    }


    public function rules(): array
    {
        return [
            '*.nombre_diseno' => [
                'required', 'max:75',
                function ($attribute, $value, $onFailure) {
                    //valid if codigo gp exist 
                    if (catDesign::where('nombre_diseno', $value)
                        ->where('id_cat_planta', $this->plant)
                        ->exists()
                    ) {
                        $onFailure('El nombre de diseño ' . $value . ' esta repetido en el documento o ya esta registrado para esta planta.');
                    }
                }
            ],
            '*.descripcion' => ['required'],
            '*.codigos_gp' => [
                'required',
                function ($attribute, $value, $onFailure) {
                    $codigos_gp = explode(',', $value);
                    foreach ($codigos_gp as $codigo_gp) {
                        if (catInks::where('codigo_gp', $codigo_gp)
                            ->where('id_cat_planta', $this->plant)
                            ->doesntExist()
                        ) {
                            $onFailure('El codigo_gp ' . $codigo_gp . ' no existe para esta planta, intenta nuevamente.');
                        }
                    }
                }
            ],
        ];
    }
}
