<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

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
                $existe = Crmovimiento::where('comprobante', $model->comprobante)
                    ->where('agencia_id', $model->agencia_id)
                    ->exists();

                if ($existe) {
                    Notification::make()
                        ->title('Comprobante Operado!')
                        ->warning()
                        ->body("El comprobante: {$model->comprobante} ya esta registrado.")
                        ->send();
                    throw ValidationException::withMessages([
                        'comprobante' => 'El número de comprobante ya ha sido registrado en esta agencia.',
                    ]);
                }
                $pago = $model->ingreso;
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
            $model->actualizado_por = Auth::id(); // ✅ Registrar quién elimina el movimiento

            $credito = Credito::find($model->credito_id);

            if ($credito) {
                if ($credito->estado == 'pagado' && $model->saldocap == 0) {
                    $credito->estado = 'desembolsado';
                }

                // 🔹 Buscar el último movimiento ANTES del eliminado
                $ultimoMovimiento = Crmovimiento::where('credito_id', $model->credito_id)
                    ->where('id', '!=', $model->id)
                    ->latest()
                    ->first();

                if ($ultimoMovimiento) {
                    // ✅ Restaurar valores previos si hay movimientos anteriores
                    $credito->saldo_capital = $ultimoMovimiento->saldocap;
                    $credito->saldo_interes = $ultimoMovimiento->saldoint;
                    $credito->saldo_mora = $ultimoMovimiento->saldomor;
                    $credito->fecha_ultimopago = $ultimoMovimiento->fecha;
                } else {
                    // ✅ Si no hay movimientos previos, cambiar estado a "solicitado"
                    $credito->estado = 'solicitado';
                    $credito->monto_desembolsado = 0;
                    $credito->saldo_capital = 0;
                    $credito->saldo_interes = 0;
                    $credito->saldo_mora = 0;
                    $credito->fecha_ultimopago = null;
                }

                $credito->save(); // 🔹 Guardar cambios
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
        'ingreso',
        'capital',
        'interes',
        'descint',
        'mora',
        'descmora',
        'otros',
        'saldocap',
        'saldoint',
        'saldomor',
        'desembolso',
        'descuentos',
        'egreso',
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
