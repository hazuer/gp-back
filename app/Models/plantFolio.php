<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class plantFolio extends Model
{
    protected $table = 'folio_planta';
    //select primary key
    protected $primaryKey = 'id_folio_planta';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'folio_entrega',
        'folio_devolucion',
        'id_cat_planta',
    ];
}
