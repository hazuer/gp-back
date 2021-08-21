<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class catDesignInks extends Model
{
    //select table
    protected $table = 'diseno_tinta';
    //select primary key
    protected $primaryKey = 'id_diseno_tinta';
    //
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_cat_diseno',
        'id_cat_tinta',
        'id_cat_estatus',
        'id_usuario_crea',
        'fecha_creacion',
        'id_usuario_modifica',
        'fecha_modificacion',
        'id_usuario_elimina',
        'fecha_eliminacion',
    ];
}
