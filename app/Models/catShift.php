<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class catShift extends Model
{
    protected $table = 'cat_turno';
    //select primary key
    protected $primaryKey = 'id_cat_turno';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'turno'
    ];
}
