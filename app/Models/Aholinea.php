<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aholinea extends Model
{
    protected $fillable = [
        'nombre',
        'tasa_interes',
        'tasa_interes_minima',
        'tasa_penalizacion',
        'plazo_minimo',
        'plazo_maximo',
        'monto_min',
        'monto_max',
        'activo',
        'condiciones',
    ];
}
