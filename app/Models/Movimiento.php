<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimiento extends Model
{
    use SoftDeletes;
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
        'caja_id',
        'fecha',
        'comprobante',
        'tipo',
        'descripcion',
        'ingreso',
        'egreso',
        'creado_por',
        'actualizado_por',
        'anulado',
    ];
    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }
    public function agencia()
    {
        return $this->belongsTo(Agencia::class);
    }
}
