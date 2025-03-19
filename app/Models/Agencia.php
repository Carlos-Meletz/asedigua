<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agencia extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'departamento',
        'municipio',
        'direccion',
        'longitude',
        'latitude',
        'telefono',
        'email',
    ];
}
