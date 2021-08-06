<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class userData extends Model
{
    //select table
    protected $table = 'datos_usuario';
    //select primar key

    //
    public $timestamps = false;
    //
    protected $primaryKey = 'id_dato_usuario';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
