<?php

namespace App\Filament\Resources\CrmovimientoResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\CrmovimientoResource;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewCrmovimiento extends ViewRecord
{
    protected static string $resource = CrmovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Html2MediaAction::make('Cpr')
                ->content(fn($record) => view('pdf.comprobante_credito', ['movimiento' => $record]))
                ->icon('heroicon-s-printer')
                ->preview()
                ->savePdf()
                ->format('letter', 'in')
                ->margin([0.3, 0.5, 0.3, 0.5])
                ->filename(fn($record) => 'CPR-' . $record->comprobante . '.pdf')
                ->authorize(fn($record) => Gate::allows('comprobante_crmovimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10),
        ];
    }
}
