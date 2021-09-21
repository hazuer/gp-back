<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class catReading extends Model
{
    protected $table = 'cat_lectura';
    //select primary key
    protected $primaryKey = 'id_cat_lectura';
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lectura'
    ];
}
