<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class catStatus extends Model
{
    protected $table = 'cat_estatus';
    //select primary key
    protected $primaryKey = 'id_cat_estatus';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_cat_estatus',
        'estatus'
    ];
}
