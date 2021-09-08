<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class catStatusOW extends Model
{
    protected $table = 'cat_estatus_ot';
    //select primary key
    protected $primaryKey = 'id_cat_estatus_ot';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'estatus_ot'
    ];
}
