<?php

namespace App\Filament\Resources\AhmovimientoResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Helpers\Funciones;
use App\Models\Ahmovimiento;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\AhmovimientoResource;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewAhmovimiento extends ViewRecord
{
    protected static string $resource = AhmovimientoResource::class;
    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Html2MediaAction::make('Cpr')
                ->content(fn($record) => view('pdf.comprobante_ahorro', ['movimiento' => $record]))
                ->icon('heroicon-s-printer')
                ->preview()
                ->savePdf()
                ->filename(fn($record) => 'CPR_(' . $record->comprobante . ')-' . $record->ahorro->cliente->nombre . '.pdf')
                ->authorize(fn($record) => Gate::allows('comprobante_ahmovimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10)
        ];
    }
}
