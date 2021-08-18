<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class orderWork extends Model
{


    //select table
    protected $table = 'orden_trabajo';

    //select primary key
    protected $primaryKey = 'id_orden_trabajo';

    //  
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_orden_trabajo',
        'orden_fabricacion',
        'id_cat_maquina',
        'id_cat_diseno',
        'cantidad_programado',
        'peso_total',
        'id_cat_turno',
        'linea',
        'id_cat_planta',
        'id_operador_responsable',
        'id_cat_estatus_ot',
        'fecha_cierre_orden',
        'id_cat_planta',
        'codigo_QR',
        'id_cat_estatus_ot',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
