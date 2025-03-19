<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fondo extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'balance',
        'descripcion',
        'activo',
    ];
}
