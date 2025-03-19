<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crelinea extends Model
{
    protected $fillable = [
        'nombre',
        'tasa_interes',
        'tasa_mora',
        'plazo_min',
        'plazo_max',
        'monto_min',
        'monto_max',
        'activo',
        'condiciones',
    ];

    public function crelineacosto()
    {
        return $this->hasMany(Crelineacosto::class);
    }
}
