<?php

namespace  App\Models;

use Illuminate\Database\Eloquent\Model;

class catPlants extends Model
{
    //select table
    protected $table = 'cat_planta';
    //select primary key
    protected $primaryKey = 'id_cat_planta';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre_planta',
        'id_cat_pais',
        'id_cat_estatus',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
