<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class catProfiles extends Model
{
    protected $table = 'cat_perfil';
    //select primary key
    protected $primaryKey = 'id_cat_perfil';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_cat_perfil',
        'perfil',
        'descripcion',
        'id_cat_estatus',
    ];
}
