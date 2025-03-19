<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crelineacosto extends Model
{
    protected $fillable = [
        'crelinea_id',
        'tipo',
        'es_porcentaje',
        'valor',
        'aplicacion',
    ];

    public function crelinea()
    {
        return $this->belongsTo(Crelinea::class);
    }
}
