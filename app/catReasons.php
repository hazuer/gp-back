<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class catReasons extends Model
{
    //select table
    protected $table = 'cat_razon';
    //select primar kye
    protected $primaryKey = 'id_cat_razon';
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $fillable = [
        'razon',
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
