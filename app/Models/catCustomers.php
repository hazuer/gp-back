<?php

namespace  App\Models;

use Illuminate\Database\Eloquent\Model;

class catCustomers extends Model
{
    //select table
    protected $table = 'cat_cliente';
    //select primary key
    protected $primaryKey = 'id_cat_cliente';
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre_cliente',
        'id_cat_planta',
        'id_cat_estatus',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
