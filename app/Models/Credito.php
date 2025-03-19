<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Credito extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agencia_id',
        'cliente_id',
        'fondo_id',
        'empleado_id',
        'crelinea_id',
        'destino_id',
        'codigo',
        'monto_solicitado',
        'monto_aprobado',
        'monto_desembolsado',
        'descuentos',
        'saldo_capital',
        'saldo_interes',
        'saldo_mora',
        'interes_anual',
        'plazo',
        'tipo_cuota',
        'tipo_plazo',
        'estado',
        'fecha_desembolso',
        'fecha_primerpago',
        'fecha_vencimiento',
        'dias_atraso',
        'cuota',
        'fecha_ultimopago',
        'numero_renovaciones',
        'notas',
    ];

    public function crmovimientos(): HasMany
    {
        return $this->hasMany(Crmovimiento::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function fondo()
    {
        return $this->belongsTo(Fondo::class);
    }

    public function agencia()
    {
        return $this->belongsTo(Agencia::class);
    }
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function crelinea()
    {
        return $this->belongsTo(Crelinea::class);
    }

    public function fiadores()
    {
        return $this->hasMany(Fiador::class);
    }
    public function garantias()
    {
        return $this->hasMany(Garantia::class);
    }
    public function destinos()
    {
        return $this->hasMany(Destino::class);
    }
}
