<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Caja;
use Filament\Tables;
use App\Models\Fondo;
use App\Models\Credito;
use App\Models\Crelinea;
use Barryvdh\DomPDF\PDF;
use Filament\Forms\Form;
use App\Helpers\Funciones;
use Filament\Tables\Table;

use App\Models\Crmovimiento;
use Filament\Actions\Action;
use App\Models\Crelineacosto;
use Filament\Resources\Resource;
use Symfony\Component\Clock\now;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\CreditoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use App\Filament\Resources\CreditoResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Filament\Resources\CreditoResource\RelationManagers\FiadorsRelationManager;
use App\Filament\Resources\CreditoResource\RelationManagers\GarantiasRelationManager;
use App\Filament\Resources\CreditoResource\RelationManagers\CrmovimientosRelationManager;

class CreditoResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'aprobacion',
            'desembolso',
            'rechazar',
            'planPagos',
            // 'reciboDesembolso',
            // 'cartaAutorizacion',
            // 'contrato',
            // 'pagare',
            'estadoCuenta',
            'fiadorCrear',
            'fiadorVer',
            'fiadorEditar',
            'fiadorEliminar',
            'garantiaCrear',
            'garantiaVer',
            'garantiaEditar',
            'garantiaEliminar',
        ];
    }
    protected static ?string $model = Credito::class;
    protected static ?string $navigationGroup = 'Créditos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')->schema([
                    Forms\Components\Select::make('cliente_id')
                        ->required()
                        ->relationship('cliente', 'nombre_completo')
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->reactive()
                        ->live(onBlur: true)
                        ->columnSpan(2)
                        ->native(false),
                    Select::make('agencia_id')
                        ->required()
                        ->relationship('agencia', 'nombre')
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->native(false)
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set) {
                            $set('codigo', sprintf('CR-%03d%08d', $state, (Credito::max('id') ?? 0) + 1));
                        })
                        ->default(fn() => Auth::user()->empleado?->agencia_id ?? 1),
                    TextInput::make('codigo')
                        ->label('Código del Crédito')
                        ->readOnly()
                        ->reactive()
                        ->live()
                        ->default(
                            fn($livewire, $get) => sprintf('CR-%03d%08d', $get('agencia_id'), (Credito::max('id') ?? 0) + 1)
                        ),
                ])->columns(4),

                Section::make('Información sobre el Crédito')->schema([
                    Select::make('fondo_id')
                        ->required()
                        ->relationship('fondo', 'nombre', fn(Builder $query) => $query->where('activo', true)->where('tipo', 'credito'))
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->default(
                            fn() => Fondo::where('activo', true)->where('tipo', 'credito')->orderBy('id')->first()?->id
                        )->columnSpan(2),
                    Select::make('crelinea_id')
                        ->label('Línea de Crédito')
                        ->relationship('crelinea', 'nombre', fn(Builder $query) => $query->where('activo', true))
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $linea = Crelinea::find($state);
                            if ($linea) {
                                $set('interes_anual', $linea->tasa_interes);
                                $set('plazo_min', $linea->plazo_min);
                                $set('plazo_max', $linea->plazo_max);
                                $set('monto_min', $linea->monto_min);
                                $set('monto_max', $linea->monto_max);
                            }
                            Funciones::calcularDescuento($get, $set);
                            // $record->calcularCuota($get, $set);
                        })
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $linea = Crelinea::find($state);
                            if ($linea) {
                                $set('interes_anual', $linea->tasa_interes);
                                $set('plazo', $linea->plazo_min);
                                $set('plazo_min', $linea->plazo_min);
                                $set('plazo_max', $linea->plazo_max);
                                $set('monto_min', $linea->monto_min);
                                $set('monto_max', $linea->monto_max);
                            }
                            Funciones::calcularDescuento($get, $set);
                            // $record->calcularCuota($get, $set);
                        })
                        ->required()
                        ->native(false),
                    Select::make('empleado_id')
                        ->label('Analista')
                        ->relationship(
                            'empleado',
                            'id',
                            fn(Builder $query) =>
                            $query->where('cargo', 'analista_creditos')->with('cliente')
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->cliente->nombre_completo)
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->required()
                        ->native(false),
                    Select::make('destino_id')
                        ->label('Destino del Crédito')
                        ->relationship('destinos', 'nombre')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nombre')
                                ->required(),
                        ])
                        ->editOptionForm([
                            Forms\Components\TextInput::make('nombre')
                                ->required(),
                        ])
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->required()
                        ->native(false),

                    TextInput::make('monto_solicitado')
                        ->label('Monto Solicitado')
                        ->numeric()
                        ->prefix('Q')
                        ->reactive()
                        ->live(onBlur: true)
                        ->default(0)
                        ->rule(fn(callable $get) => "between:{$get('monto_min')},{$get('monto_max')}")
                        ->required()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            Funciones::calcularDescuento($get, $set);
                        }),

                    TextInput::make('descuentos')
                        ->label('Descuentos')
                        ->numeric()
                        ->prefix('Q')
                        ->required(),

                    TextInput::make('interes_anual')
                        ->label('Interés Anual')
                        ->numeric()
                        ->suffix('%')
                        ->step(0.01)
                        ->readOnly(),

                    TextInput::make('plazo')
                        ->label('Plazo')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->live(onBlur: true)
                        ->rule(fn(callable $get) => "between:{$get('plazo_min')},{$get('plazo_max')}")
                        ->suffixIcon('heroicon-o-clock')
                        ->suffix('Meses')
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            Funciones::calcularFechaVencimiento($get, $set);
                            Funciones::calcularFechaPrimerPago($get, $set);
                        }),

                    // Select::make('tipo_plazo')
                    //     ->label('Tipo de Plazo')
                    //     ->options([
                    //         'diario' => 'Diario',
                    //         'semanal' => 'Semanal',
                    //         'quincenal' => 'Quincenal',
                    //         'mensual' => 'Mensual',
                    //     ])
                    //     ->required()
                    //     ->reactive()
                    //     ->live(onBlur: true)
                    //     ->afterStateUpdated(
                    //         function (callable $set, callable $get) {
                    //             Funciones::calcularFechaPrimerPago($get, $set);
                    //             Funciones::calcularFechaVencimiento($get, $set);
                    //         }
                    //     )->native(false),

                    Select::make('tipo_cuota')
                        ->label('Tipo de Cuota')
                        ->options([
                            'frances' => 'Sistema Francés (Cuotas Fijas)',
                            'aleman' => 'Sistema Alemán (Decreciente)',
                            'americano' => 'Sistema Americano (Pago Único)',
                            'flat' => 'Flat',
                        ])
                        ->default('frances')
                        ->required()
                        ->reactive()
                        ->live(onBlur: true)
                        ->native(false),

                    TextInput::make('cuota')
                        ->label('Cuota')
                        ->numeric()
                        ->prefix('Q')
                        ->required()
                        ->readOnly(),

                    DatePicker::make('fecha_desembolso')->label('Fecha')
                        ->default(now())
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            function (callable $set, callable $get) {
                                Funciones::calcularFechaPrimerPago($get, $set);
                                Funciones::calcularFechaVencimiento($get, $set);
                            }
                        ),
                    DatePicker::make('fecha_primerpago')->label('Fecha del Primer Pago')->readOnly(),
                    DatePicker::make('fecha_vencimiento')->label('Fecha de Vencimiento')->readOnly(),
                    ToggleButtons::make('estado')
                        ->options([
                            'solicitado' => 'Solicitado',
                            'aprobado' => 'Aprobado',
                            'desembolsado' => 'Desembolsado',
                            'rechazado' => 'Rechazado',
                            'pendiente' => 'Pendiente',
                            'pagado' => 'Pagado',
                            'vencido' => 'Vencido',
                        ])
                        ->colors([
                            'solicitado' => 'gray',
                            'aprobado' => 'success',
                            'desembolsado' => 'info',
                            'rechazado' => 'danger',
                            'pendiente' => 'warning',
                            'pagado' => 'green',
                            'vencido' => 'red',
                        ])
                        ->default('solicitado')
                        ->required()
                        ->inline()
                        ->visibleOn('edit'),
                    Actions::make([
                        Actions\Action::make('generar_plan')
                            ->label('Ver Plan')
                            ->action(fn(callable $get, callable $set) => $set('plan_pagos', Funciones::calcularPlanPagos($get, $set)))
                            ->color('primary')
                            ->icon('heroicon-o-document-text'),
                    ]),
                    RichEditor::make('notas')
                        ->label('Notas')
                        ->columnSpanFull(),
                    TableRepeater::make('plan_pagos')
                        ->label('Plan de Pagos')
                        ->schema([
                            TextInput::make('nocuota')->label('No.')->readOnly(),
                            DatePicker::make('fecha')->label('Fecha')->native(false)->readOnly(),
                            TextInput::make('cuota')->label('Cuota')->prefix('Q')->readOnly(),
                            TextInput::make('interes')->label('Interés')->prefix('Q')->readOnly(),
                            TextInput::make('capital')->label('Capital')->prefix('Q')->readOnly(),
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
                        ->hidden(fn(callable $get) => empty($get('plan_pagos')))
                        ->columnSpan('full'),
                ])
                    ->columns(4)->visible(fn($get) => !empty($get('cliente_id'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fondo.nombre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('crelinea.nombre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monto')
                    ->getStateUsing(fn($record) => match ($record->estado) {
                        'solicitado' => $record->monto_solicitado,
                        'aprobado' => $record->monto_aprobado,
                        'desembolsado' => $record->monto_desembolsado,
                        default => $record->monto_desembolsado,
                    })
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('descuentos')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('interes_anual')
                    ->numeric()
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plazo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tipo_cuota')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('tipo_plazo')
                //     ->searchable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'solicitado' => 'gray',
                        'aprobado' => 'success',
                        'desembolsado' => 'info',
                        'rechazado' => 'danger',
                        'pendiente' => 'warning',
                        'pagado' => 'primary',
                        'vencido' => 'Red',
                    }),
                Tables\Columns\TextColumn::make('fecha_desembolso')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_primerpago')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dias_atraso')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fecha_ultimopago')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('numero_renovaciones')
                    ->numeric()
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
            ])->defaultSort('estado', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('aprobar')
                    ->label('Aprobación')
                    ->modalWidth('sm')
                    ->authorize(fn() => Gate::allows('aprobacion_credito'))
                    ->extraModalWindowAttributes(['style' => 'width: 95vw; max-width: 1024px;
                        @media (min-width: 1024px) {
                            max-width: 50vw;
                        }'])
                    ->icon('heroicon-c-check-circle')
                    ->action(function ($record, array $data) {
                        // Lógica para aprobar el préstamo
                        $record->update([
                            'crelinea_id' => $data['crelinea_id'],
                            'destino_id' => $data['destino_id'],
                            'monto_aprobado' => $data['monto_aprobado'],
                            'descuentos' => $data['descuentos'],
                            'interes_anual' => $data['interes_anual'],
                            'plazo' => $data['plazo'],
                            'tipo_cuota' => $data['tipo_cuota'],
                            'cuota' => $data['cuota'],
                            'fecha_desembolso' => $data['fecha_desembolso'],
                            'fecha_primerpago' => $data['fecha_primerpago'],
                            'fecha_vencimiento' => $data['fecha_vencimiento'],
                            'notas' => $data['notas'],
                            'estado' => 'aprobado',
                        ]);
                        Notification::make()
                            ->title('Crédito aprobado correctamente')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Section::make('Información General')
                            ->schema([
                                Placeholder::make('agencia_id')
                                    ->label('Agencia')
                                    ->content(fn($record) => $record->agencia->nombre),
                                Placeholder::make('cliente_id')
                                    ->label('Cliente')
                                    ->content(fn($record) => $record->cliente->nombre_completo),
                                Placeholder::make('codigo')
                                    ->label('Codigo del Credito')
                                    ->content(fn($record) => $record->codigo),
                            ])->columns(3),
                        Section::make('Datos de Aprobación')
                            ->schema([
                                Select::make('crelinea_id')
                                    ->label('Línea de Crédito')
                                    ->relationship('crelinea', 'nombre')
                                    ->default(fn($record) => $record->crelinea_id)
                                    ->searchable()
                                    ->optionsLimit(5)
                                    ->preload()
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateHydrated(
                                        function ($state, callable $set, callable $get) {
                                            $linea = Crelinea::find($state);
                                            if ($linea) {
                                                $set('plazo_min', $linea->plazo_min);
                                                $set('plazo_max', $linea->plazo_max);
                                                $set('monto_min', $linea->monto_min);
                                                $set('monto_max', $linea->monto_max);
                                            }
                                        }
                                    )
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $linea = Crelinea::find($state);
                                        if ($linea) {
                                            $set('interes_anual', $linea->tasa_interes);
                                            $set('plazo_min', $linea->plazo_min);
                                            $set('plazo_max', $linea->plazo_max);
                                            $set('monto_min', $linea->monto_min);
                                            $set('monto_max', $linea->monto_max);
                                            Funciones::calcularDescuento($get, $set);
                                            Funciones::calcularCuota($get, $set);
                                        }
                                    })
                                    ->required()
                                    ->native(false),
                                Select::make('destino_id')
                                    ->label('Destino del Crédito')
                                    ->relationship('destinos', 'nombre')
                                    ->default(fn($record) => $record->destino_id)
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nombre')
                                            ->required(),
                                    ])
                                    ->editOptionForm([
                                        Forms\Components\TextInput::make('nombre')
                                            ->required(),
                                    ])
                                    ->searchable()
                                    ->optionsLimit(5)
                                    ->preload()
                                    ->required()
                                    ->native(false),
                                TextInput::make('monto_aprobado')
                                    ->label('Monto a Aprobar')
                                    ->numeric()
                                    ->default(fn($record) => $record->monto_solicitado)
                                    ->prefix('Q')
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rule(fn(callable $get) => "between:{$get('monto_min')},{$get('monto_max')}")
                                    ->required()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        Funciones::calcularDescuento($get, $set);
                                        Funciones::calcularCuota($get, $set);
                                    }),

                                TextInput::make('descuentos')
                                    ->label('Descuentos')
                                    ->default(fn($record) => $record->descuentos)
                                    ->numeric()
                                    ->prefix('Q')
                                    ->required(),

                                TextInput::make('interes_anual')
                                    ->label('Interés Anual')
                                    ->numeric()
                                    ->default(fn($record) => $record->interes_anual)
                                    ->suffix('%')
                                    ->step(0.01)
                                    ->readOnly(),

                                TextInput::make('plazo')
                                    ->label('Plazo')
                                    ->numeric()
                                    ->default(fn($record) => $record->plazo)
                                    ->required()
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rule(fn(callable $get) => "between:{$get('plazo_min')},{$get('plazo_max')}")
                                    ->suffixIcon('heroicon-o-clock')
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        Funciones::calcularFechaVencimiento($get, $set);
                                        Funciones::calcularFechaPrimerPago($get, $set);
                                        Funciones::calcularCuota($get, $set);
                                    }),

                                // Select::make('tipo_plazo')
                                //     ->label('Tipo de Plazo')
                                //     ->options([
                                //         'diario' => 'Diario',
                                //         'semanal' => 'Semanal',
                                //         'quincenal' => 'Quincenal',
                                //         'mensual' => 'Mensual',
                                //     ])
                                //     ->default(fn($record) => $record->tipo_plazo)
                                //     ->required()
                                //     ->reactive()
                                //     ->live(onBlur: true)
                                //     ->afterStateUpdated(
                                //         function (callable $set, callable $get) {
                                //             Funciones::calcularFechaPrimerPago($get, $set);
                                //             Funciones::calcularFechaVencimiento($get, $set);
                                //             Funciones::calcularCuota($get, $set);
                                //         }
                                // )->native(false),


                                Select::make('tipo_cuota')
                                    ->label('Tipo de Cuota')
                                    ->options([
                                        'frances' => 'Sistema Francés (Cuotas Fijas)',
                                        'aleman' => 'Sistema Alemán (Decreciente)',
                                        'americano' => 'Sistema Americano (Pago Único)',
                                        'flat' => 'Flat',
                                    ])
                                    ->default(fn($record) => $record->tipo_cuota)
                                    ->required()
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(
                                        function (callable $set, callable $get) {
                                            Funciones::calcularCuota($get, $set);
                                        }
                                    )
                                    ->native(false),

                                TextInput::make('cuota')
                                    ->label('Cuota')
                                    ->numeric()
                                    ->default(fn($record) => $record->cuota)
                                    ->prefix('Q')
                                    ->readOnly(),

                                DatePicker::make('fecha_desembolso')->label('Fecha')
                                    ->default(fn($record) => $record->fecha_desembolso)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(
                                        function (callable $set, callable $get) {
                                            Funciones::calcularFechaPrimerPago($get, $set);
                                            Funciones::calcularFechaVencimiento($get, $set);
                                        }
                                    ),
                                DatePicker::make('fecha_primerpago')->label('Fecha del Primer Pago')
                                    ->default(fn($record) => $record->fecha_primerpago),
                                DatePicker::make('fecha_vencimiento')->label('Fecha de Vencimiento')
                                    ->default(fn($record) => $record->fecha_vencimiento),

                                RichEditor::make('notas')
                                    ->columnSpanFull()
                                    ->label('Notas')
                                    ->default(fn($record) => $record->notas),
                            ])->columns(3),
                    ])
                    ->color('success')
                    ->visible(fn(Credito $credito) => $credito->estado === 'solicitado')
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('desembolsar')
                    ->label('Desembolso')
                    ->modalWidth('sm')
                    ->authorize(fn() => Gate::allows('desembolso_credito'))
                    ->extraModalWindowAttributes(['style' => 'width: 95vw; max-width: 1024px;
                        @media (min-width: 1024px) {
                            max-width: 50vw;
                        }'])
                    ->icon('heroicon-s-clipboard-document-check')
                    ->action(function ($record, array $data) {
                        Funciones::registrarMovimientoDesembolso($record, $data);
                    })
                    ->form([
                        Section::make('Información General')
                            ->schema([
                                Placeholder::make('agencia_id')
                                    ->label('Agencia')
                                    ->content(fn($record) => $record->agencia->nombre),
                                Placeholder::make('cliente_id')
                                    ->label('Cliente')
                                    ->content(fn($record) => $record->cliente->nombre_completo),
                                Placeholder::make('codigo')
                                    ->label('Codigo del Credito')
                                    ->content(fn($record) => $record->codigo),
                            ])->columns(3),
                        Section::make('Registrar Movimiento')->schema([
                            Forms\Components\TextInput::make('comprobante')
                                ->required(),
                            Select::make('tipo')
                                ->label('Tipo de Pago')
                                ->options([
                                    'efectivo' => 'Efectivo',
                                    'banco' => 'Deposito a Banco',
                                ])
                                ->default('efectivo')
                                ->required()
                                ->native(false),
                        ])->columns(3),
                        Section::make('Datos de Desembolso')
                            ->schema([
                                Select::make('crelinea_id')
                                    ->label('Línea de Crédito')
                                    ->relationship('crelinea', 'nombre')
                                    ->default(fn($record) => $record->crelinea_id)
                                    ->searchable()
                                    ->optionsLimit(5)
                                    ->preload()
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateHydrated(
                                        function ($state, callable $set, callable $get) {
                                            $linea = Crelinea::find($state);
                                            if ($linea) {
                                                $set('plazo_min', $linea->plazo_min);
                                                $set('plazo_max', $linea->plazo_max);
                                                $set('monto_min', $linea->monto_min);
                                                $set('monto_max', $linea->monto_max);
                                            }
                                        }
                                    )
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $linea = Crelinea::find($state);
                                        if ($linea) {
                                            $set('interes_anual', $linea->tasa_interes);
                                            $set('plazo_min', $linea->plazo_min);
                                            $set('plazo_max', $linea->plazo_max);
                                            $set('monto_min', $linea->monto_min);
                                            $set('monto_max', $linea->monto_max);
                                            Funciones::calcularDescuento($get, $set);
                                            Funciones::calcularCuota($get, $set);
                                        }
                                    })
                                    ->required()
                                    ->native(false),
                                Select::make('destino_id')
                                    ->label('Destino del Crédito')
                                    ->relationship('destinos', 'nombre')
                                    ->default(fn($record) => $record->destino_id)
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nombre')
                                            ->required(),
                                    ])
                                    ->editOptionForm([
                                        Forms\Components\TextInput::make('nombre')
                                            ->required(),
                                    ])
                                    ->searchable()
                                    ->optionsLimit(5)
                                    ->preload()
                                    ->required()
                                    ->native(false),

                                TextInput::make('monto_desembolsado')
                                    ->label('Monto a Desembolsar')
                                    ->numeric()
                                    ->default(fn($record) => $record->monto_aprobado)
                                    ->prefix('Q')
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rule(fn(callable $get) => "between:{$get('monto_min')},{$get('monto_max')}")
                                    ->required()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        Funciones::calcularDescuento($get, $set);
                                        Funciones::calcularCuota($get, $set);
                                    }),

                                TextInput::make('descuentos')
                                    ->label('Descuentos')
                                    ->default(fn($record) => $record->descuentos)
                                    ->numeric()
                                    ->prefix('Q')
                                    ->required(),

                                TextInput::make('interes_anual')
                                    ->label('Interés Anual')
                                    ->numeric()
                                    ->default(fn($record) => $record->interes_anual)
                                    ->suffix('%')
                                    ->step(0.01)
                                    ->readOnly(),

                                TextInput::make('plazo')
                                    ->label('Plazo')
                                    ->numeric()
                                    ->default(fn($record) => $record->plazo)
                                    ->required()
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rule(fn(callable $get) => "between:{$get('plazo_min')},{$get('plazo_max')}")
                                    ->suffixIcon('heroicon-o-clock')
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        Funciones::calcularFechaVencimiento($get, $set);
                                        Funciones::calcularFechaPrimerPago($get, $set);
                                        Funciones::calcularCuota($get, $set);
                                    }),

                                // Select::make('tipo_plazo')
                                //     ->label('Tipo de Plazo')
                                //     ->options([
                                //         'diario' => 'Diario',
                                //         'semanal' => 'Semanal',
                                //         'quincenal' => 'Quincenal',
                                //         'mensual' => 'Mensual',
                                //     ])
                                //     ->default(fn($record) => $record->tipo_plazo)
                                //     ->required()
                                //     ->reactive()
                                //     ->live(onBlur: true)
                                //     ->afterStateUpdated(
                                //         function (callable $set, callable $get) {
                                //             Funciones::calcularFechaPrimerPago($get, $set);
                                //             Funciones::calcularFechaVencimiento($get, $set);
                                //         }
                                //     )->native(false),

                                Select::make('tipo_cuota')
                                    ->label('Tipo de Cuota')
                                    ->options([
                                        'frances' => 'Sistema Francés (Cuotas Fijas)',
                                        'aleman' => 'Sistema Alemán (Decreciente)',
                                        'americano' => 'Sistema Americano (Pago Único)',
                                        'flat' => 'Flat',
                                    ])
                                    ->default(fn($record) => $record->tipo_cuota)
                                    ->required()
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(
                                        function (callable $set, callable $get) {
                                            Funciones::calcularCuota($get, $set);
                                        }
                                    )
                                    ->native(false),

                                TextInput::make('cuota')
                                    ->label('Cuota')
                                    ->numeric()
                                    ->default(fn($record) => $record->cuota)
                                    ->prefix('Q')
                                    ->readOnly(),

                                DatePicker::make('fecha_desembolso')->label('Fecha')
                                    ->default(fn($record) => $record->fecha_desembolso)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(
                                        function (callable $get, callable $set) {
                                            Funciones::calcularFechaPrimerPago($get, $set);
                                            Funciones::calcularFechaVencimiento($get, $set);
                                        }
                                    ),
                                DatePicker::make('fecha_primerpago')->label('Fecha del Primer Pago')
                                    ->default(fn($record) => $record->fecha_primerpago),
                                DatePicker::make('fecha_vencimiento')->label('Fecha de Vencimiento')
                                    ->default(fn($record) => $record->fecha_vencimiento),

                                RichEditor::make('notas')
                                    ->columnSpanFull()
                                    ->label('Notas')
                                    ->default(fn($record) => $record->notas),
                            ])->columns(4),
                    ])
                    ->visible(fn(Credito $credito) => $credito->estado === 'aprobado')
                    ->color('success')
                    ->requiresConfirmation(),
                ActionGroup::make([
                    Html2MediaAction::make('plan')
                        ->label('Plan')
                        ->content(fn($record) => view('pdf.plan_pagos', ['credito' => $record, 'plan' => Funciones::obtenerPlan($record)]))
                        ->icon('heroicon-s-printer')
                        ->preview()
                        ->savePdf()
                        ->filename(fn($record) => 'Plan_' . $record->codigo . '.pdf')
                        ->format('letter', 'in')
                        ->margin([0.3, 0.5, 0.3, 0.5])
                        ->authorize(fn() => Gate::allows('planPagos_credito'))
                        ->visible(fn($record) => in_array($record->estado, ['aprobado', 'desembolsado', 'vencido', 'pagado'])),
                    // Html2MediaAction::make('recibo')
                    //     ->label('Recibo Desembolso')
                    //     ->content(fn($record) => view('pdf.recibo_desembolso', ['credito' => $record]))
                    //     ->icon('heroicon-s-printer')
                    //     ->preview()
                    //     ->savePdf()
                    //     ->margin([0.3, 0.5, 0.3, 0.5])
                    //     ->filename(fn($record) => 'RecDesembolso_' . $record->codigo . '.pdf')
                    //     ->format('letter', 'in')
                    //     ->authorize(fn() => Gate::allows('reciboDesembolso_credito'))
                    //     ->visible(fn($record) => in_array($record->estado, ['desembolsado'])),
                    // Html2MediaAction::make('autorizacion')
                    //     ->label('Carta Autorizacion')
                    //     ->content(fn($record) => view('pdf.autorizacion_credito', ['credito' => $record]))
                    //     ->icon('heroicon-s-printer')
                    //     ->preview()
                    //     ->savePdf()
                    //     ->margin([0.3, 0.5, 0.3, 0.5])
                    //     ->filename(fn($record) => 'CrtAutorizacion_' . $record->codigo . '.pdf')
                    //     ->format('letter', 'in')
                    //     ->authorize(fn() => Gate::allows('cartaAutorizacion_credito'))
                    //     ->visible(fn($record) => in_array($record->estado, ['aprobado', 'desembolsado'])),
                    // Html2MediaAction::make('contrato')
                    //     ->label('Contrato')
                    //     ->content(fn($record) => view('pdf.contrato_credito', ['credito' => $record]))
                    //     ->icon('heroicon-s-printer')
                    //     ->preview()
                    //     ->savePdf()
                    //     ->margin([0.3, 0.5, 0.3, 0.5])
                    //     ->filename(fn($record) => 'Contrato_' . $record->codigo . '.pdf')
                    //     ->format('letter', 'in')
                    //     ->authorize(fn() => Gate::allows('contrato_credito'))
                    //     ->visible(fn($record) => in_array($record->estado, ['desembolsado'])),
                    // Html2MediaAction::make('pagare')
                    //     ->label('Pagare')
                    //     ->content(fn($record) => view('pdf.pagare_credito', ['credito' => $record]))
                    //     ->icon('heroicon-s-printer')
                    //     ->preview()
                    //     ->savePdf()
                    //     ->margin([0.3, 0.5, 0.3, 0.5])
                    //     ->filename(fn($record) => 'Pagare_' . $record->codigo . '.pdf')
                    //     ->format('letter', 'in')
                    //     ->authorize(fn() => Gate::allows('pagare_credito'))
                    //     ->visible(fn($record) => in_array($record->estado, ['desembolsado'])),
                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-s-x-circle')
                        ->action(function ($record, $data) {
                            $record->update([
                                'notas' => $data('notas'),
                                'estado' => 'rechazado',
                            ]);
                            Notification::make()
                                ->title('Crédito Rechazado')
                                ->danger()
                                ->send();
                        })
                        ->form([
                            RichEditor::make('notas')->columnSpanFull()->label('Notas')->default(fn($record) => $record->notas),
                        ])
                        ->color('danger')
                        ->visible(fn(Credito $credito) => in_array($credito->estado, ['solicitado', 'aprobado']))
                        ->authorize(fn() => Gate::allows('rechazar_credito'))
                        ->requiresConfirmation(),
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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getRelations(): array
    {
        return [
            CrmovimientosRelationManager::class,
            FiadorsRelationManager::class,
            GarantiasRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditos::route('/'),
            'create' => Pages\CreateCredito::route('/create'),
            'edit' => Pages\EditCredito::route('/{record}/edit'),
            'view' => Pages\ViewCredito::route('/{record}'),
        ];
    }
}
