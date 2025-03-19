<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crmovimiento extends Model
{
    use SoftDeletes;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->creado_por = Auth::id();
            $model->actualizado_por = Auth::id();
            $credito = Credito::find($model->credito_id);
            if (!$model->desembolso) {
                $pago = $model->pago;
                $otros = 0;
                $interes = $model->interes - $model->descint;
                $mora = $model->mora - $model->descmora;

                if ($pago >= $mora) {
                    $pago -= $mora;
                } else {
                    $mora = $pago;
                    $pago = 0;
                }

                if ($pago >= $interes) {
                    $pago -= $interes;
                } else {
                    $interes = $pago;
                    $pago = 0;
                }
                if ($pago > $credito->saldo_capital) {
                    $otros = $pago - $credito->saldo_capital;
                    $capital = $credito->saldo_capital;
                    $credito->estado = 'pagado';
                    $model->notas = '*PAGADO*';
                } else {
                    $capital = $pago;
                }
                $model->capital = $capital;
                $model->interes = $interes;
                $model->mora = $mora;
                $model->otros = $otros;
                $model->saldocap = $credito->saldo_capital - $capital;
                $model->saldoint = $model->interes - $interes;
                $model->saldomor = $model->mora - $mora;

                if ($credito) {
                    $credito->saldo_capital = $credito->saldo_capital - $capital;
                    $credito->saldo_interes = $model->interes - $interes;
                    $credito->saldo_mora = $model->mora - $mora;
                    $credito->fecha_ultimopago = $model->fecha;
                    $credito->save();
                }
            }
        });

        static::updating(function ($model) {
            $model->actualizado_por = Auth::id();
        });

        static::deleting(function ($model) {
            $model->actualizado_por = Auth::id();
            $model->actualizado_por = Auth::id();
            $credito = Credito::find($model->credito_id);

            if ($credito) {
                if ($credito->estado == 'pagado' && $model->saldocap == 0) {
                    $credito->estado = 'desembolsado';
                }
                // Restaurar la fecha del último pago al último movimiento antes de este
                $ultimoMovimiento = Crmovimiento::where('credito_id', $model->credito_id)
                    ->where('id', '!=', $model->id) // Excluir el movimiento que se está eliminando
                    ->latest()
                    ->first();
                // Revertir los valores restados en la creación del movimiento
                $credito->saldo_capital = $ultimoMovimiento->saldocap;
                $credito->saldo_interes = $ultimoMovimiento->saldoint;
                $credito->saldo_mora = $ultimoMovimiento->saldomor;
                $credito->fecha_ultimopago = $ultimoMovimiento->fecha;
                $credito->save();
            }
        });
    }

    protected $fillable = [
        'agencia_id',
        'caja_id',
        'credito_id',
        'fecha',
        'comprobante',
        'tipo',
        'pago',
        'capital',
        'interes',
        'descint',
        'mora',
        'descmora',
        'otros',
        'microseguro',
        'saldocap',
        'saldoint',
        'saldomor',
        'desembolso',
        'descuentos',
        'atraso',
        'notas',
        'creado_por',
        'actualizado_por',
        'anulado',
    ];

    public function credito()
    {
        return $this->belongsTo(Credito::class);
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
