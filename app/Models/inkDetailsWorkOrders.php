<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class inkDetailsWorkOrders extends Model
{
    protected $table = 'ot_detalle_tinta';
    //select primary key
    protected $primaryKey = 'id_ot_detalle_tinta';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $fillable = [
        'id_orden_trabajo',
        'id_cat_tinta',
        'lote',
        'id_cat_tara',
        'peso_individual',
        'utiliza_ph',
        'mide_viscosidad',
        'utiliza_filtro',
        'porcentaje_variacion',
        'id_cat_estatus',
        'peso_individual_gp',
        'peso_individual_cliente',
        'id_cat_lectura_gp',
        'id_cat_lectura_cliente',
        'existe_diferencia_entrega',
        'id_cat_razon',
        'total_diferencia_entrega',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion'
    ];
}
