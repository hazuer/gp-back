<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class userLogs extends Model
{

    //select table
    protected $table = 'log_usuarios';
    //select primary key
    protected $primaryKey = 'id_log';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'fecha',
        'id_usuario',
        'tabla',
        'id_tabla',
        'actividad',
        'id_cat_planta'
    ];
}
