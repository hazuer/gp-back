<?php

namespace  App\Models;

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
        'orden_trabajo_of',
        'id_cat_maquina',
        'id_cat_diseno',
        'cantidad_programado',
        'peso_total',
        'id_cat_turno',
        'linea',
        'id_cat_planta',
        'id_operador_responsable',
        'fecha_cierre_orden',
        'codigo_QR',
        'adiciones',
        'orden_cobrada',
        'fecha_cobro',
        'folio_entrega',
        'id_cat_estatus_ot',
        'fecha_entrega',
        'peso_entrega_total',
        'id_cliente_autoriza',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
