<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referencia extends Model
{
    protected $fillable = [
        'cliente_id',
        'tipo',
        'nombre',
        'telefono',
    ];
}
