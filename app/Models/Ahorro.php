<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ahorro extends Model
{
    protected $fillable = [
        'agencia_id',
        'cliente_id',
        'fondo_id',
        'aholinea_id',
        'numero_cuenta',
        'tipo',
        'saldo',
        'saldo_contrato',
        'interes_acumulado',
        'interes_anual',
        'estado',
        'fecha_apertura',
        'plazo',
        'fecha_vencimiento',
        'nuevo',
        'numero_renovaciones',
        'notas',
    ];


    public function ahmovimientos(): HasMany
    {
        return $this->hasMany(Ahmovimiento::class);
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

    public function aholinea()
    {
        return $this->belongsTo(Aholinea::class);
    }

    public function beneficiarios()
    {
        return $this->hasMany(Beneficiario::class);
    }
}
