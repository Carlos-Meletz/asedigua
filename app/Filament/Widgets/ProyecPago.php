<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Credito;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class ProyecPago extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(Credito::where('saldo_capital', '>', 0))
            ->columns([
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monto')
                    ->getStateUsing(fn($record) => match ($record->estado) {
                        'solicitado' => $record->monto_solicitado,
                        'desembolsado' => $record->monto_desembolsado,
                        default => $record->monto_desembolsado,
                    })
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_ultimopago')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_capital')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cuota')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dias_atraso')
                    ->numeric()
                    ->sortable(),
            ])->filters([
                Filter::make('fecha_desembolso')
                    ->form([
                        DatePicker::make('Desde'),
                        DatePicker::make('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Desde'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_desembolso', '>=', $date),
                            )
                            ->when(
                                $data['Hasta'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_desembolso', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent);
    }
}
