<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficiario extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'dpi',
        'dep_dpi',
        'mun_dpi',
        'relacion',
        'profesion',
        'telefono',
        'direccion',
    ];


    public function Ahorro()
    {
        return $this->belongsTo(Ahorro::class);
    }
}
