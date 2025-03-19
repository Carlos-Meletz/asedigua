<?php

namespace App\Filament\Resources\CreditoResource\Pages;

use Filament\Actions;
use App\Helpers\Funciones;
use App\Models\Ahmovimiento;
use App\Models\Crmovimiento;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\CreditoResource;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewCredito extends ViewRecord
{
    protected static string $resource = CreditoResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Html2MediaAction::make('plan')
                ->label('Plan de Pagos')
                ->content(fn($record) => view('pdf.plan_pagos', ['credito' => $record, 'plan' => Funciones::obtenerPlan($record)]))
                ->icon('heroicon-s-printer')
                ->preview()
                ->savePdf()
                ->filename(fn($record) => 'Plan_' . $record->codigo . '.pdf')
                ->format('letter', 'in')
                ->margin([0.3, 0.5, 0.3, 0.5])
                ->authorize(fn() => Gate::allows('planPagos_credito'))
                ->visible(fn($record) => in_array($record->estado, ['aprobado', 'desembolsado', 'vencido', 'pagado'])),
            Html2MediaAction::make('estadoCuenta')
                ->label('Estado de Cuenta')
                ->content(fn($record) => view('pdf.est-cuenta_credito', ['credito' => $record, 'movimientos' => Crmovimiento::where('credito_id', $record->id)->where('anulado', false)->orderBy('fecha', 'asc')->get()]))
                // ->content(fn($record) => view('pdf.est-cuenta_credito', ['credito' => $record, 'movimientos' => Crmovimiento::where('credito_id', $record->id)->where('anulado', false)->orderBy('fecha', 'asc')->get(), 'calculo' => Funciones::calcularpago($record)]))
                ->icon('heroicon-s-printer')
                ->preview()
                ->scale(5)
                ->savePdf()
                ->filename(fn($record) => 'CREstCuenta_' . $record->codigo . '.pdf')
                ->format('letter', 'in')
                ->margin([0.3, 0.5, 0.3, 0.5])
                ->authorize(fn() => Gate::allows('estadoCuenta_credito'))
                ->visible(fn($record) => in_array($record->estado, ['desembolsado', 'vencido', 'pagado'])),
            Actions\EditAction::make(),
        ];
    }
}
