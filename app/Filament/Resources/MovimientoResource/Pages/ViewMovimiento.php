<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\MovimientoResource;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewMovimiento extends ViewRecord
{
    protected static string $resource = MovimientoResource::class;
    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Html2MediaAction::make('Cpr')
                ->content(fn($record) => view('pdf.comprobante_movimiento', ['movimiento' => $record]))
                ->icon('heroicon-s-printer')
                ->preview()
                ->format('letter', 'in')
                ->margin([0.3, 0.5, 0.3, 0.5])
                ->savePdf()
                ->filename(fn($record) => 'CPR-' . $record->comprobante . '.pdf')
                ->authorize(fn($record) => Gate::allows('comprobante_movimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10)
        ];
    }
}
