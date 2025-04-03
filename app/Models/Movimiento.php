<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Movimiento extends Model
{
    use SoftDeletes;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->creado_por = Auth::id();
            $model->actualizado_por = Auth::id();

            $existe = Ahmovimiento::where('comprobante', $model->comprobante)
                ->where('agencia_id', $model->agencia_id)
                ->exists() ||
                Crmovimiento::where('comprobante', $model->comprobante)
                ->where('agencia_id', $model->agencia_id)
                ->exists() ||
                Movimiento::where('comprobante', $model->comprobante)
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
