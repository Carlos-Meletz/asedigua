<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajo extends Model
{
    protected $fillable = [
        'cliente_id',
        'empresa',
        'cargo',
        'ingreso_mensual',
        'antiguedad',
    ];
}
