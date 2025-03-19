<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ahmovimiento extends Model
{
    use SoftDeletes;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $cuenta = Ahorro::find($model->ahorro_id);
            if ($cuenta && !$cuenta->nuevo) {
                $cuenta->saldo = $model->saldo;
                $cuenta->interes_acumulado = $cuenta->interes_acumulado + $model->interes_acumulado;
                $cuenta->save();
            }

            $model->monto = $model->deposito;
            $model->creado_por = Auth::id();
            $model->actualizado_por = Auth::id();
        });


        static::updating(function ($model) {
            $model->actualizado_por = Auth::id();
            $cuenta = Ahorro::find($model->ahorro_id);
            // Verificar si es el único movimiento de la cuenta

            if ($cuenta) {

                $cuenta->saldo = $model->saldo;
                $cuenta->interes_acumulado += $model->interes_acumulado;
                $cuenta->save();
            }
            $model->monto = $model->deposito - $model->retiro - $model->penalizacion;
        });
        static::deleting(function ($model) {
            $model->actualizado_por = Auth::id();
            $cuenta = Ahorro::find($model->ahorro_id);
            $movimientosRestantes = Ahmovimiento::where('ahorro_id', $model->ahorro_id)->withoutTrashed()->count();
            if ($cuenta) {
                if ($movimientosRestantes == 1) { // Porque aún no se ha eliminado el actual
                    // $cuenta->estado = 'inactiva';
                    $cuenta->nuevo = true;
                }
                $cuenta->saldo = $cuenta->saldo - $model->deposito + $model->retiro + $model->penalizacion;
                $cuenta->interes_acumulado = $cuenta->interes_acumulado + $model->interes;
                $cuenta->save();
            }
        });
    }

    protected $fillable = [
        'agencia_id',
        'caja_id',
        'ahorro_id',
        'fecha',
        'comprobante',
        'tipo',
        'deposito',
        'retiro',
        'interes',
        'penalizacion',
        'interes_acumulado',
        'monto',
        'saldo',
        'notas',
        'creado_por',
        'actualizado_por',
        'anulado',
    ];

    public function ahorro()
    {
        return $this->belongsTo(Ahorro::class);
    }
    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }
    public function agencia()
    {
        return $this->belongsTo(Agencia::class);
    }
}
