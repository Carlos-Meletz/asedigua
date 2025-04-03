<?php

namespace App\Filament\Exports;

use App\Models\Credito;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CreditoExporter extends Exporter
{
    protected static ?string $model = Credito::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('agencia.nombre'),
            ExportColumn::make('codigo'),
            ExportColumn::make('cliente.nombre_completo'),
            ExportColumn::make('empleado.cliente.nombre_completo')->label('Analista'),
            ExportColumn::make('crelinea.nombre'),
            ExportColumn::make('destino.nombre'),
            ExportColumn::make('monto_solicitado')
                ->enabledByDefault(false),
            ExportColumn::make('monto_desembolsado'),
            ExportColumn::make('descuentos'),
            ExportColumn::make('saldo_capital')->enabledByDefault(false),
            ExportColumn::make('saldo_interes')->enabledByDefault(false),
            ExportColumn::make('saldo_mora')->enabledByDefault(false),
            ExportColumn::make('interes_anual'),
            ExportColumn::make('plazo'),
            ExportColumn::make('tipo_cuota'),
            ExportColumn::make('estado'),
            ExportColumn::make('fecha_desembolso'),
            ExportColumn::make('fecha_primerpago'),
            ExportColumn::make('fecha_vencimiento'),
            ExportColumn::make('dias_atraso'),
            ExportColumn::make('cuota'),
            ExportColumn::make('fecha_ultimopago'),
            ExportColumn::make('numero_renovaciones'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your credito export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
