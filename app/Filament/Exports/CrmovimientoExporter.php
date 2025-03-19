<?php

namespace App\Filament\Exports;

use App\Models\Crmovimiento;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CrmovimientoExporter extends Exporter
{
    protected static ?string $model = Crmovimiento::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('agencia_id'),
            // ExportColumn::make('caja_id'),
            ExportColumn::make('credito_id'),
            ExportColumn::make('fecha'),
            ExportColumn::make('comprobante'),
            ExportColumn::make('tipo'),
            ExportColumn::make('pago'),
            ExportColumn::make('capital'),
            ExportColumn::make('intes'),
            ExportColumn::make('descint'),
            ExportColumn::make('mora'),
            ExportColumn::make('descmora'),
            ExportColumn::make('otros'),
            // ExportColumn::make('microseguro'),
            // ExportColumn::make('saldocap'),
            // ExportColumn::make('saldoint'),
            // ExportColumn::make('saldomor'),
            ExportColumn::make('desembolso'),
            ExportColumn::make('descuentos'),
            ExportColumn::make('atraso'),
            ExportColumn::make('notas'),
            ExportColumn::make('creado_por'),
            // ExportColumn::make('actualizado_por'),
            // ExportColumn::make('anulado'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your crmovimiento export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
