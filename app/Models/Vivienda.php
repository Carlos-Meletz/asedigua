<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vivienda extends Model
{
    protected $fillable = [
        'cliente_id',
        'tipo',
        'direccion',
        'tiempo_residencia',
        'condiciones_vivienda',
        'servicio_agua',
        'servicio_energia',
        'servicio_alcantarillado',
        'servicio_internet',
        'servicio_telefono',
        'valor_estimado',
        'monto_alquiler',
        'nombre_propietario',
        'referencia_ubicacion',
    ];
}
