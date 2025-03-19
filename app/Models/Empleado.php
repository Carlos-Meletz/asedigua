<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $fillable = [
        'cliente_id',
        'agencia_id',
        'cargo',
        'salario',
        'fecha_ingreso',
        'fecha_salida'
    ];

    // Relación inversa con Persona
    public function agencia()
    {
        return $this->belongsTo(Agencia::class);
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con User
    public function user()
    {
        return $this->hasOne(User::class);
    }
}
