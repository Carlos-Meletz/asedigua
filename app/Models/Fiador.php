<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fiador extends Model
{
    protected $fillable = [
        'credito_id',
        'tipo',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'edad',
        'estado_civil',
        'dep_dpi',
        'mun_dpi',
        'dpi',
        'relacion',
        'profesion',
        'direccion',
        'telefono',
        'firma',
    ];
    public function Credito()
    {
        return $this->belongsTo(Credito::class);
    }
}
