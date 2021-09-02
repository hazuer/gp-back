<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class systemConfigurations extends Model
{
    //select table
    protected $table = 'configuracion_sistema';

    //select primary key
    protected $primaryKey = 'id_configuracion_sistema';

    //  
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id_cat_planta',
        'campo_lote',
        'campo_cantidad_programada',
        'utiliza_tara',
        'campo_linea',
        'requiere_turno',
        'variacion_maxima',
        'porcentaje_variacion_aceptado',
        'utiliza_ph',
        'mide_viscosidad',
        'utiliza_filtro',
        'id_cat_estatus',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
