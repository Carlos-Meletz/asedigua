<?php

namespace App\Filament\Resources\AhorroResource\Pages;

use Filament\Actions;
use App\Models\Ahmovimiento;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\AhorroResource;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewAhorro extends ViewRecord
{
    protected static string $resource = AhorroResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Html2MediaAction::make('print')
                ->label('Estado de Cuenta')
                ->content(fn($record) => view('pdf.est-cuenta_ahorro', ['ahorro' => $record, 'movimientos' => Ahmovimiento::where('ahorro_id', $record->id)->orderBy('fecha', 'asc')->get()]))
                ->icon('heroicon-s-printer')
                ->preview()
                ->savePdf()
                ->filename(fn($record) => 'EstCuenta_' . $record->numero_cuenta . '.pdf')
                ->format('letter', 'in')
                ->visible(fn($record) => !$record->nuevo)
                ->authorize(fn() => Gate::allows('estadoCuenta_ahorro')),
            Actions\EditAction::make(),
        ];
    }
}
