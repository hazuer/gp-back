<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class catDesign extends Model
{
    //select table
    protected $table = 'cat_diseno';
    //select primary key
    protected $primaryKey = 'id_cat_diseno';
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $fillable = [
        'nombre_diseno',
        'descripcion',
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
