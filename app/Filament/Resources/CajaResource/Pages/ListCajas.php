<?php

namespace App\Filament\Resources\CajaResource\Pages;

use App\Models\Caja;
use Filament\Actions;
use App\Models\Agencia;
use Illuminate\Support\Facades\Gate;
use App\Filament\Resources\CajaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;


class ListCajas extends ListRecords
{
    protected static string $resource = CajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('descargar_pdf')
                ->label('Aperturar Todos')
                ->action(function () {
                    // Obtener cajas abiertas
                    $cajasAbiertas = Caja::where('abierta', true)->get();

                    if ($cajasAbiertas->isNotEmpty()) {
                        // Listar agencias con cajas abiertas
                        $agenciasAbiertas = $cajasAbiertas->map(fn($caja) => $caja->agencia->nombre)->join(', ');

                        // Enviar notificación de advertencia
                        Notification::make()
                            ->title('Cajas aún abiertas')
                            ->danger()
                            ->body("Las siguientes agencias aún tienen cajas abiertas: $agenciasAbiertas.")
                            ->send();

                        return;
                    }

                    // Si todas las cajas están cerradas, crear nuevas cajas
                    $agencias = Agencia::all();
                    foreach ($agencias as $agencia) {
                        Caja::create([
                            'agencia_id' => $agencia->id,
                            'fecha_apertura' => now(),
                            'abierta' => true,
                        ]);
                    }

                    // Notificación de éxito
                    Notification::make()
                        ->title('Cajas creadas')
                        ->success()
                        ->body('Se han creado cajas para todas las agencias.')
                        ->send();
                })
                ->color('primary')
                ->authorize(fn() => Gate::allows('aperturarTodo_caja'))
                ->requiresConfirmation(),
        ];
    }
}
