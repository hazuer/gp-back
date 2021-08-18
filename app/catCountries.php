<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class catCountries extends Model
{

    //select table
    protected $table = 'cat_pais';
    //select primary key
    protected $primaryKey = 'id_cat_pais';
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $fillable = [
        'nombre_pais',
        'id_cat_estatus',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
