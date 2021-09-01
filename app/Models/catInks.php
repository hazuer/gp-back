<?php

namespace  App\Models;

use Illuminate\Database\Eloquent\Model;

class catInks extends Model
{
    //select table
    protected $table = 'cat_tinta';
    //select primary key
    protected $primaryKey = 'id_cat_tinta';
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre_tinta',
        'codigo_cliente',
        'codigo_gp',
        'aditivo',
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
