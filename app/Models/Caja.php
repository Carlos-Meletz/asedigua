<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->creado_por = Auth::id();
            $model->actualizado_por = Auth::id();
        });


        static::updating(function ($model) {
            $model->actualizado_por = Auth::id();
        });
    }


    protected $fillable = [
        'agencia_id',
        'fecha_apertura',
        'fecha_cierre',
        'ahingresos',
        'ahegresos',
        'cringresos',
        'cregresos',
        'otingresos',
        'otegresos',
        'totalingresos',
        'totalegresos',
        'saldo',
        'creado_por',
        'actualizado_por',
        'abierta',
    ];

    public function agencia()
    {
        return $this->belongsTo(Agencia::class);
    }

    public function ahmovimientos(): HasMany
    {
        return $this->hasMany(Ahmovimiento::class);
    }
    public function crmovimientos(): HasMany
    {
        return $this->hasMany(Crmovimiento::class);
    }
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }
}
