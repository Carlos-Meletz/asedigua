<?php

namespace App\Filament\Widgets;

use App\Helpers\Funciones;
use Carbon\Carbon;
use Filament\Tables;
use App\Models\Credito;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class ProyecPago extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected $fechaBase;

    protected static ?string $heading = 'Proyección de Pagos de Créditos';

    // Guardar el total de pagos proyectados
    public $totalPagosProyectados = 0;
    public ?float $totalCapital = 0;
    public ?float $totalInteres = 0;
    public ?float $totalMora = 0;
    public ?float $totalPago = 0;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Credito::query()
                    ->where('saldo_capital', '>', 0)
            )
            ->columns([
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_desembolso')
                    ->label('Desembolso')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date(),
                Tables\Columns\TextColumn::make('fecha_pago')
                    ->label('Fecha Proyectada')
                    ->getStateUsing(function ($record) {

                        $fechaBase = $this->fechaBase ?? now();
                        $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);

                        return $fechaProyectada;
                    })
                    ->sortable()
                    ->date(),

                Tables\Columns\TextColumn::make('fecha_ultimopago')
                    ->label('Ultimo Pago')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_desembolsado')
                    ->money('GTQ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


                Tables\Columns\TextColumn::make('saldo_capital')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cuota')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('atraso')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $fechaBase = $this->fechaBase ?? now();
                        $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                        $calculo = Funciones::calcularpago($record->id, $fechaProyectada);

                        // Si no hay pago o fue antes de la fecha proyectada, calcular días de atraso
                        return $calculo['atraso'];
                    })
                    ->suffix(' días')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capital')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $fechaBase = $this->fechaBase ?? now();
                        $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                        $calculo = Funciones::calcularpago($record->id, $fechaProyectada);

                        // Si no hay pago o fue antes de la fecha proyectada, calcular días de atraso
                        return $calculo['capital'];
                    })
                    ->money('GTQ')
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interes')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $fechaBase = $this->fechaBase ?? now();
                        $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                        $calculo = Funciones::calcularpago($record->id, $fechaProyectada);

                        // Si no hay pago o fue antes de la fecha proyectada, calcular días de atraso
                        return $calculo['interes'];
                    })
                    ->color('warning')
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mora')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $fechaBase = $this->fechaBase ?? now();
                        $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                        $calculo = Funciones::calcularpago($record->id, $fechaProyectada);

                        // Si no hay pago o fue antes de la fecha proyectada, calcular días de atraso
                        return $calculo['mora'];
                    })
                    ->color('danger')
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pago')
                    ->numeric()
                    ->label('A pagar')
                    ->getStateUsing(function ($record) {
                        $fechaBase = $this->fechaBase ?? now();
                        $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                        $calculo = Funciones::calcularpago($record->id, $fechaProyectada);

                        // Si no hay pago o fue antes de la fecha proyectada, calcular días de atraso
                        return $calculo['ingreso'];
                    })
                    ->color('success')
                    ->money('GTQ')
                    ->sortable(),

            ])
            ->filters([
                Filter::make('proyeccion')
                    ->form([
                        \Filament\Forms\Components\Grid::make(3)
                            ->schema([

                                Select::make('tipo_filtro')
                                    ->label('Filtrar por')
                                    ->options([
                                        'fecha' => 'Fecha específica',
                                        'rango' => 'Rango de fechas',
                                        // 'mes' => 'Mes específico',
                                    ])
                                    ->default('fecha')
                                    ->reactive()
                                    ->required(),

                                DatePicker::make('fecha')
                                    ->label('Fecha')
                                    ->visible(fn($get) => $get('tipo_filtro') === 'fecha'),

                                DatePicker::make('desde')
                                    ->label('Desde')
                                    ->visible(fn($get) => $get('tipo_filtro') === 'rango'),

                                DatePicker::make('hasta')
                                    ->label('Hasta')
                                    ->visible(fn($get) => $get('tipo_filtro') === 'rango'),
                            ]),

                    ])->columnSpanFull()

                    ->query(function (Builder $query, array $data) {
                        if (isset($data['tipo_filtro'])) {
                            if ($data['tipo_filtro'] === 'fecha' && !empty($data['fecha'])) {
                                $query->whereDay('fecha_primerpago', Carbon::parse($data['fecha'])->day);
                                $this->fechaBase = Carbon::parse($data['fecha']);
                            } elseif ($data['tipo_filtro'] === 'rango' && !empty($data['desde']) && !empty($data['hasta'])) {
                                $desde = Carbon::parse($data['desde'])->day;
                                $hasta = Carbon::parse($data['hasta'])->day;

                                $query->whereRaw("DATE_FORMAT(fecha_primerpago, '%d') BETWEEN ? AND ?", [
                                    $desde,
                                    $hasta,
                                ]);
                                $this->fechaBase = \Carbon\Carbon::parse($data['desde']);
                            } else {
                                $this->fechaBase = now();
                            }
                        }
                        // Calcula el total de pagos
                        $this->totalCapital = $query->get()->sum(function ($record) {
                            $fechaBase = $this->fechaBase ?? now();
                            $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                            $calculo = Funciones::calcularpago($record->id, $fechaProyectada);
                            return $calculo['capital'] ?? 0;
                        });
                        $this->totalInteres = $query->get()->sum(function ($record) {
                            $fechaBase = $this->fechaBase ?? now();
                            $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                            $calculo = Funciones::calcularpago($record->id, $fechaProyectada);
                            return $calculo['interes'] ?? 0;
                        });
                        $this->totalMora = $query->get()->sum(function ($record) {
                            $fechaBase = $this->fechaBase ?? now();
                            $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                            $calculo = Funciones::calcularpago($record->id, $fechaProyectada);
                            return $calculo['mora'] ?? 0;
                        });
                        $this->totalPago = $query->get()->sum(function ($record) {
                            $fechaBase = $this->fechaBase ?? now();
                            $fechaProyectada = $fechaBase->setDay(Carbon::Parse($record->fecha_primerpago)->day);
                            $calculo = Funciones::calcularpago($record->id, $fechaProyectada);
                            return $calculo['ingreso'] ?? 0;
                        });
                        return $query;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('Capital')
                    ->label(function () {
                        // $records = $this->getTableRecords();
                        // $total = $records->sum(fn($record) => $record->capital ?? 0);
                        return 'Capital: Q' . number_format($this->totalCapital, 2);
                    })
                    ->disabled()
                    ->color('info'),
                Action::make('Interes')
                    ->label(function () {
                        // $records = $this->getTableRecords();
                        // $total = $records->sum(fn($record) => $record->cuota ?? 0);
                        return 'Interes: Q' . number_format($this->totalInteres, 2);
                    })
                    ->disabled()
                    ->color('warning'),
                Action::make('Mora')
                    ->label(function () {
                        // $records = $this->getTableRecords();
                        // $total = $records->sum(fn($record) => $record->cuota ?? 0);
                        return 'Mora: Q' . number_format($this->totalMora, 2);
                    })
                    ->disabled()
                    ->color('danger'),
                Action::make('Total')
                    ->label(function () {
                        // $records = $this->getTableRecords();
                        // $total = $records->sum(fn($record) => $record->cuota ?? 0);
                        return 'Total Proyectado: Q' . number_format($this->totalPago, 2);
                    })
                    ->disabled()
                    ->color('success'),
            ]);
    }


    // protected int | string | array $columnSpan = 'full';
    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query(Credito::where('saldo_capital', '>', 0))
    //         ->columns([
    //             Tables\Columns\TextColumn::make('agencia.nombre')
    //                 ->numeric()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //             Tables\Columns\TextColumn::make('cliente.nombre_completo')
    //                 ->numeric()
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('codigo')
    //                 ->searchable(),
    // Tables\Columns\TextColumn::make('monto')
    //     ->getStateUsing(fn($record) => match ($record->estado) {
    //         'solicitado' => $record->monto_solicitado,
    //         'desembolsado' => $record->monto_desembolsado,
    //         default => $record->monto_desembolsado,
    //     })
    //     ->numeric()
    //     ->money('GTQ')
    //     ->sortable(),
    //             Tables\Columns\TextColumn::make('fecha_ultimopago')
    //                 ->date()
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('saldo_capital')
    //                 ->numeric()
    //                 ->money('GTQ')
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('cuota')
    //                 ->numeric()
    //                 ->money('GTQ')
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('dias_atraso')
    //                 ->numeric()
    //                 ->sortable(),
    //         ])->filters([
    //             Filter::make('fecha_desembolso')
    //                 ->form([
    //                     DatePicker::make('Desde'),
    //                     DatePicker::make('Hasta'),
    //                 ])
    //                 ->query(function (Builder $query, array $data): Builder {
    //                     return $query
    //                         ->when(
    //                             $data['Desde'],
    //                             fn(Builder $query, $date): Builder => $query->whereDate('fecha_desembolso', '>=', $date),
    //                         )
    //                         ->when(
    //                             $data['Hasta'],
    //                             fn(Builder $query, $date): Builder => $query->whereDate('fecha_desembolso', '<=', $date),
    //                         );
    //                 }),
    //         ], layout: FiltersLayout::AboveContent);
    // }
}
