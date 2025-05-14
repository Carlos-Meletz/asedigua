<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Caja;
use Filament\Tables;
use App\Models\Credito;
use Filament\Forms\Form;
use App\Helpers\Funciones;
use Filament\Tables\Table;
use App\Models\Crmovimiento;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\CrmovimientoExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CrmovimientoResource\Pages;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Tables\Filters\TernaryFilter;


class CrmovimientoResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Crmovimiento::class;
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'comprobante',
            'descuento',
            'anular',
        ];
    }
    protected static ?string $navigationLabel = 'Transacciones';
    protected static ?string $navigationGroup = 'Créditos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('credito_id')
                            ->required()
                            ->relationship('credito', 'id', fn(Builder $query) => $query->whereIn('estado', ['desembolsado', 'vencido', 'atrasado'])->with('cliente'))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->cliente->nombre_completo . ' | ' . $record->codigo)
                            ->searchable()
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                calcularpago($state, $get, $set);
                            })
                            ->optionsLimit(5)
                            ->preload()
                            ->native(false)
                            ->columnSpan(2),
                        Select::make('agencia_id')
                            ->label('Agencia')
                            ->relationship('agencia', 'nombre')
                            ->searchable()
                            ->optionsLimit(5)
                            ->preload()
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $cajaActiva = Caja::where('agencia_id', $state)
                                    ->where('abierta', true)
                                    ->first();
                                if ($cajaActiva) {
                                    $set('caja_id', $cajaActiva->id);
                                } else {
                                    $set('caja_id', '');
                                    Notification::make()
                                        ->title('No hay caja abierta')
                                        ->warning()
                                        ->body('No hay una caja activa para esta agencia.')
                                        ->send();
                                }
                            })
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('fecha')
                            ->required()
                            ->label('Fecha')
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                calcularpago($get('credito_id'), $get, $set);
                            })
                            ->default(now()),
                        Forms\Components\Hidden::make('caja_id')
                            ->required(),
                        Forms\Components\Hidden::make('atraso')
                            ->required(),
                    ])
                    ->columns(4),
                Section::make('Informacion sobre el Crédito')
                    ->schema([
                        Fieldset::make('Datos del Crédito')->schema([
                            TextInput::make('desembolsos')->prefix('Q')->readOnly()->reactive()->live()->default(''),
                            DatePicker::make('entrega')->readOnly()->reactive()->live()->default(''),
                            DatePicker::make('vencimiento')->readOnly()->reactive()->live()->default(''),
                            DatePicker::make('ultimopago')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('plazo')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('cuota')->prefix('Q')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('atraso')->readOnly()->reactive()->live()->default(''),
                        ])->columns(7),
                        Fieldset::make('Saldos')->schema([
                            TextInput::make('saldoCapital')->columnSpan(2)->prefix('Q')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('saldoInteres')->prefix('Q')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('saldoMora')->prefix('Q')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('ciclos')->readOnly()->reactive()->live()->default(''),
                            TextInput::make('total')->columnSpan(2)->prefix('Q')->label('Cancelación')->readOnly()->reactive()->live()->default(''),
                        ])->columns(7),
                    ])->collapsed()
                    ->visible(fn($get) => !empty($get('credito_id')))
                    ->visibleOn('create'),
                Section::make('Registro del Pago')
                    ->schema([
                        Group::make([
                            Group::make()->schema([
                                Select::make('tipo')
                                    ->label('Tipo de Pago')
                                    ->options([
                                        'efectivo' => 'Efectivo',
                                        'banco' => 'Deposito a Banco',
                                    ])
                                    ->default(fn($record) => ($record) ? $record->tipo : 'efectivo')
                                    ->required()
                                    ->native(false),
                                Forms\Components\TextInput::make('comprobante')
                                    ->required()
                                    ->integer()
                                    ->default(fn($record) => ($record) ? $record->comprobante : ''),
                                Forms\Components\TextInput::make('ingreso')
                                    ->required()
                                    ->label('Pago Sugerido')
                                    ->numeric()
                                    ->default(fn($record) => ($record) ? $record->ingreso : '')
                                    ->prefix('Q'),
                            ]),

                            Group::make([
                                Group::make([
                                    Forms\Components\TextInput::make('capital')
                                        ->required()
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('Q')
                                        ->default(fn($record) => ($record) ? $record->capital : 0),
                                    Forms\Components\Toggle::make('descuentos')
                                        ->default(false)
                                        ->live()
                                        ->visibleOn('create')
                                        ->visible(fn() => Gate::allows('descuento_crmovimiento'))
                                        ->inline(false),
                                ])->columns(2),
                                Group::make([
                                    Forms\Components\TextInput::make('interes')
                                        ->required()
                                        ->numeric()
                                        // ->readOnly()
                                        ->prefix('Q')
                                        ->default(fn($record) => ($record) ? $record->interes : 0),
                                    Forms\Components\TextInput::make('descint')
                                        ->required()
                                        ->numeric()
                                        ->prefix('- Q')
                                        ->default(fn($record) => ($record) ? $record->descint : 0)
                                        ->visible(fn($get) => $get('descuentos')),
                                    // ->visibleOn('view'),
                                ])->columns(2),
                                Group::make([
                                    Forms\Components\TextInput::make('mora')
                                        ->required()
                                        // ->readOnly()
                                        ->numeric()
                                        ->prefix('Q')
                                        ->default(fn($record) => ($record) ? $record->mora : 0),
                                    Forms\Components\TextInput::make('descmora')
                                        ->required()
                                        ->numeric()
                                        ->prefix('- Q')
                                        ->default(fn($record) => ($record) ? $record->descmora : 0)
                                        ->visible(fn($get) => $get('descuentos')),
                                ])->columns(2),
                                Forms\Components\TextInput::make('otros')
                                    ->required()
                                    ->numeric()
                                    ->hiddenOn('create')
                                    ->prefix('Q')
                                    ->default(fn($record) => ($record) ? $record->otros : 0),
                            ]),
                        ])->columns(2),
                        Forms\Components\Textarea::make('notas')
                            ->columnSpanFull(),
                        Actions::make([
                            Actions\Action::make('historial')
                                ->label('Ver Historial')
                                ->action(fn(callable $get, callable $set) => $set('estado_cuenta', Funciones::obtenerHistorial($get, $set)))
                                ->color('primary')
                                ->icon('heroicon-o-document-text'),
                        ]),
                        TableRepeater::make('estado_cuenta')
                            ->label('Historial de Abonos')
                            ->schema([
                                DatePicker::make('fcontable')->label('Contable')->native(false)->readOnly(),
                                DatePicker::make('fvalor')->label('Valor')->native(false)->readOnly(),
                                TextInput::make('atraso')->label('Atraso')->readOnly(),
                                TextInput::make('ingreso')->label('Pago')->prefix('Q')->readOnly(),
                                TextInput::make('capital')->label('Capital')->prefix('Q')->readOnly(),
                                TextInput::make('interes')->label('Interés')->prefix('Q')->readOnly(),
                                TextInput::make('mora')->label('Mora')->prefix('Q')->readOnly(),
                                TextInput::make('saldo')->label('Saldo')->prefix('Q')->readOnly(),
                            ])
                            ->reorderable(false)
                            ->cloneable(false)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reactive()
                            ->live(onBlur: true)
                            ->hidden(fn(callable $get) => empty($get('estado_cuenta')))
                            ->columnSpan('full'),
                    ])->visible(fn($get) => !empty($get('credito_id'))),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credito.cliente.nombre_completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credito.codigo')
                    ->numeric()
                    ->searchable()
                    ->label('No. Crédito')
                    ->sortable(),
                Tables\Columns\TextColumn::make('comprobante')
                    ->searchable()
                    ->label('No. CPR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ingreso')
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capital')
                    ->money('GTQ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('interes')
                    ->money('GTQ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('descint')
                    ->money('GTQ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('mora')
                    ->money('GTQ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('descmora')
                    ->money('GTQ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('egreso')
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\IconColumn::make('anulado')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                TernaryFilter::make('solo_caja_abierta')
                    ->label('Solo caja abierta')
                    ->trueLabel('Sí')
                    ->falseLabel('No')
                    ->default(true)
                    ->queries(
                        true: fn($query) => $query->whereHas('caja', fn($q) => $q->where('abierta', true)),
                        false: fn($query) => $query, // Muestra todos los movimientos si está en "No"
                    ),
            ])
            ->headerActions([
                ExportAction::make()->exporter(CrmovimientoExporter::class)->formats([
                    ExportFormat::Xlsx,
                ])
            ])
            ->actions([
                Html2MediaAction::make('Cpr')
                    ->content(fn($record) => view('pdf.comprobante_credito', ['movimiento' => $record]))
                    ->icon('heroicon-s-printer')
                    ->preview()
                    ->savePdf()
                    ->format('letter', 'in')
                    ->margin([0, 0.5, 0.3, 0.5])
                    ->filename(fn($record) => 'CPR-' . $record->comprobante . '.pdf')
                    ->authorize(fn($record) => Gate::allows('comprobante_crmovimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('anular')
                        ->label('Anular')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($record, $data) {
                            $credito = Credito::find($record->credito_id);
                            if ($credito) {
                                if ($credito->estado == 'pagado' && $record->saldocap == 0) {
                                    $credito->estado = 'desembolsado';
                                }
                                $ultimoMovimiento = Crmovimiento::where('credito_id', $record->credito_id)
                                    ->where('id', '!=', $record->id)
                                    ->whereNull('deleted_at')
                                    ->latest()
                                    ->first();
                                $credito->saldo_capital = $ultimoMovimiento->saldocap;
                                $credito->saldo_interes = $ultimoMovimiento->saldoint;
                                $credito->saldo_mora = $ultimoMovimiento->saldomor;
                                $credito->fecha_ultimopago = $ultimoMovimiento->fecha;
                                $credito->save();
                            }
                            $record->update([
                                'notas' => '-Transaccion Anulada-',
                                'ingreso' => 0,
                                'capital' => 0,
                                'interes' => 0,
                                'descint' => 0,
                                'mora' => 0,
                                'descmora' => 0,
                                'otros' => 0,
                                'salodcap' => 0,
                                'saldoint' => 0,
                                'saldomor' => 0,
                                'desembolso' => 0,
                                'descuentos' => 0,
                                'egreso' => 0,
                                'anulado' => true,
                            ]);
                            Notification::make()
                                ->title('Transaccion Anulada - ' . $record->comprobante)
                                ->warning()
                                ->send();
                        })
                        ->color('danger')
                        ->authorize(fn() => Gate::allows('anular_crmovimiento'))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrmovimientos::route('/'),
            'create' => Pages\CreateCrmovimiento::route('/create'),
            'edit' => Pages\EditCrmovimiento::route('/{record}/edit'),
            'view' => Pages\ViewCrmovimiento::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

function calcularpago($state, $get, $set)
{
    $credito = Credito::find($state);
    if ($credito) {

        //MOSTRAR DATOS DEL CREDITO
        $set('desembolsos', $credito->monto_desembolsado);
        $set('entrega', $credito->fecha_desembolso);
        $set('vencimiento', $credito->fecha_vencimiento);
        $set('plazo', $credito->plazo);
        $set('cuota', $credito->cuota);
        $set('ultimopago', $credito->fecha_ultimopago);
        $set('atraso', $credito->atraso);
        $set('saldoCapital', $credito->saldo_capital);
        $set('saldoInteres', $credito->saldo_interes);
        $set('saldoMora', $credito->saldo_mora);
        $set('ciclos', $credito->numero_renovaciones);

        $fechaDesembolso = Carbon::parse($credito->fecha_desembolso);
        $fecha_ultimo_pago = Carbon::parse($credito->fecha_ultimopago);
        $fechaActual = Carbon::parse($get('fecha'));
        $plazo = $credito->plazo;
        $montoTotal = $credito->monto_desembolsado;
        $tasaInteresAnual = $credito->crelinea->tasa_interes;
        $tasaInteresMensual = $tasaInteresAnual / 12 / 100;
        $tasaInteresDiario = $tasaInteresAnual / 365 / 100;

        if ($credito->tipo_cuota == 'frances') {
            $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
            $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

            if ($fechaProximoVencimiento > $fechaActual) {
                $diasAtraso = 0;
            } else {
                $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
            }

            $cuotaMensual = round(($montoTotal * $tasaInteresMensual) / (1 - pow(1 + $tasaInteresMensual, -$plazo)), 2);
            $meses_transcurridos = intval($fechaDesembolso->diffInMonths($fechaActual));
            $meses_pagados = intval($fechaDesembolso->diffInMonths($fecha_ultimo_pago));
            $saldo = $montoTotal;
            $capital_vencido = 0;
            $capital_acumulado = 0;
            $capital_mes = 0;

            $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');

            for ($i = 1; $i <= $meses_transcurridos; $i++) {
                $interes_mes = $saldo * $tasaInteresMensual;
                $capital_mes = $cuotaMensual - $interes_mes;
                $capital_acumulado += $capital_mes;
                if ($i > $meses_pagados) {
                    if ($capital_acumulado > $montoTotal) {
                        $capital_acumulado = $montoTotal;
                    }
                    $capital_vencido = $capital_acumulado - $capitalpagado;
                }
                $saldo -= $capital_mes;
            }
            $interes_apagar = $credito->saldo_capital * $tasaInteresDiario * $dias_transcurridos;
            $capital_vencido = max(0, $capital_vencido);
            $mora = 0;
            $pago_sugerido = $interes_apagar;
            if ($diasAtraso > 0) {
                $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
            }
        }
        if ($credito->tipo_cuota == 'aleman') {
            $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
            $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

            if ($fechaProximoVencimiento > $fechaActual) {
                $diasAtraso = 0;
            } else {
                $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
            }

            $meses_transcurridos = intval($fechaDesembolso->diffInMonths($fechaActual));
            $meses_pagados = intval($fechaDesembolso->diffInMonths($fecha_ultimo_pago));
            $saldo = $montoTotal;
            $capital_vencido = 0;
            $capital_acumulado = 0;
            $capital_mes = 0;

            $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');

            for ($i = 1; $i <= $meses_transcurridos; $i++) {
                $cuotaMensual = $saldo / ($plazo - ($i - 1)) + ($saldo * $tasaInteresMensual);
                $interes_mes = $saldo * $tasaInteresMensual;
                $capital_mes = $cuotaMensual - $interes_mes;
                $capital_acumulado += $capital_mes;
                if ($i > $meses_pagados) {
                    if ($capital_acumulado > $montoTotal) {
                        $capital_acumulado = $montoTotal;
                    }
                    $capital_vencido = $capital_acumulado - $capitalpagado;
                }
                $saldo -= $capital_mes;
            }
            $interes_apagar = $credito->saldo_capital * $tasaInteresDiario * $dias_transcurridos;
            $capital_vencido = max(0, $capital_vencido);
            $mora = 0;
            $pago_sugerido = $interes_apagar;
            if ($diasAtraso > 0) {
                $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
            }
        }
        if ($credito->tipo_cuota == 'americano') {
            $fechaProximoVencimiento = Carbon::parse($credito->fecha_vencimiento)->format('Y-m-d');
            $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

            $capital_vencido = 0;
            $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');
            if ($fechaProximoVencimiento > $fechaActual) {
                $diasAtraso = 0;
            } else {
                $capital_vencido = $montoTotal - $capitalpagado;
                $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
            }

            $interes_apagar = $credito->saldo_capital * $tasaInteresDiario * $dias_transcurridos;
            $capital_vencido = max(0, $capital_vencido);
            $mora = 0;
            $pago_sugerido = $interes_apagar;
            if ($diasAtraso > 0) {
                $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
            }
        }
        if ($credito->tipo_cuota == 'flat') {
            $fechaProximoVencimiento = Carbon::parse($credito->fecha_desembolso)->addMonths(floor(($fecha_ultimo_pago->diffInMonths(Carbon::parse($credito->fecha_desembolso), true)) + 1))->format('Y-m-d');
            $dias_transcurridos = intval($fecha_ultimo_pago->diffInDays($fechaActual));

            if ($fechaProximoVencimiento > $fechaActual) {
                $diasAtraso = 0;
            } else {
                $diasAtraso = intval($fechaActual->diffInDays($fechaProximoVencimiento, true));
            }

            $cuotaMensual = round(($montoTotal + ($montoTotal * (($tasaInteresAnual / 100) / 12) * $plazo)) / $plazo, 2);

            $meses_transcurridos = intval($fechaDesembolso->diffInMonths($fechaActual));
            $meses_pagados = intval($fechaDesembolso->diffInMonths($fecha_ultimo_pago));
            $saldo = $montoTotal;
            $capital_vencido = 0;
            $capital_acumulado = 0;
            $capital_mes = 0;

            $capitalpagado = Crmovimiento::where('credito_id', $credito->id)->whereNull('deleted_at')->sum('capital');

            for ($i = 1; $i <= $meses_transcurridos; $i++) {
                $interes_mes = $montoTotal * $tasaInteresMensual;
                $capital_mes = $cuotaMensual - ($interes_mes);
                $capital_acumulado += $capital_mes;
                if ($i > $meses_pagados) {
                    if ($capital_acumulado > $montoTotal) {
                        $capital_acumulado = $montoTotal;
                    }
                    $capital_vencido = $capital_acumulado - $capitalpagado;
                }
                $saldo -= $capital_mes;
            }
            $interes_apagar = $montoTotal * $tasaInteresMensual;
            $capital_vencido = max(0, $capital_vencido);
            $mora = 0;

            $pago_sugerido = $interes_apagar + $capital_vencido;
            if ($diasAtraso > 0) {
                $interes_apagar = $montoTotal * $tasaInteresMensual * ($meses_transcurridos - $meses_pagados);
                $tasaMoraDiario = $credito->crelinea->tasa_mora / 365 / 100;
                $mora = ($capital_vencido * $tasaMoraDiario) * $diasAtraso;
                $pago_sugerido = $capital_vencido + $interes_apagar + $mora;
            }
        }

        $set('capital', number_format($capital_vencido, 2, '.', ''));
        $set('interes', number_format($interes_apagar, 2, '.', ''));
        $set('mora', number_format($mora, 2, '.', ''));
        $set('atraso', $diasAtraso);
        $set('total', number_format($credito->saldo_capital + $interes_apagar + $mora, 2, '.', ''));
        $set('saldo_capital', number_format($credito->saldo_capital, 2, '.', ''));
        $set('ingreso', number_format($pago_sugerido, 2, '.', ''));
    }
}
