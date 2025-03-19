<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Caja;
use Filament\Tables;
use App\Models\Fondo;
use App\Models\Ahorro;
use App\Models\Aholinea;
use App\Models\Empleado;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Ahmovimiento;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\AhorroResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use App\Filament\Resources\AhorroResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\AhorroResource\RelationManagers\AhmovimientosRelationManager;
use App\Filament\Resources\AhorroResource\RelationManagers\BeneficiariosRelationManager;
use Laravel\SerializableClosure\Serializers\Native;

class AhorroResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'beneficiarioCrear',
            'beneficiarioVer',
            'beneficiarioEditar',
            'beneficiarioEliminar',
            'aperturar',
            'renovar',
            'estadoCuenta',
            // 'contrato',
        ];
    }
    protected static ?string $model = Ahorro::class;
    protected static ?string $navigationLabel = 'Cuentas';
    protected static ?string $navigationGroup = 'Ahorros';

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
                    Forms\Components\Select::make('agencia_id')
                        ->required()
                        ->relationship('agencia', 'nombre')
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set) {
                            $set('numero_cuenta', sprintf('CH-%03d%08d', $state, (Ahorro::max('id') ?? 0) + 1));
                        })
                        ->default(fn() => Auth::user()->empleado?->agencia_id ?? 1)
                        ->native(false),
                    Forms\Components\TextInput::make('numero_cuenta')
                        ->readOnly()
                        ->reactive()
                        ->live()
                        ->default(
                            fn($livewire, $get) => sprintf('CH-%03d%08d', $get('agencia_id'), (Ahorro::max('id') ?? 0) + 1)
                        ),
                ])->columns(4),

                Section::make('Datos de la Cuenta de Ahorro')->schema([
                    Forms\Components\Select::make('fondo_id')
                        ->required()
                        ->relationship('fondo', 'nombre', fn(Builder $query) => $query->where('activo', true)->where('tipo', 'ahorro'))
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->default(
                            fn() => Fondo::where('activo', true)->where('tipo', 'ahorro')->orderBy('id')->first()?->id
                        ),
                    Forms\Components\Select::make('aholinea_id')
                        ->relationship('aholinea', 'nombre', fn(Builder $query) => $query->where('activo', true))
                        ->searchable()
                        ->reactive()
                        ->live(onBlur: true)
                        ->optionsLimit(5)
                        ->preload()
                        ->required()
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $linea = Aholinea::find($state);
                            if ($linea) {
                                $set('interes_anual', $linea->tasa_interes);
                                $set('plazo', $linea->plazo_minimo);
                                $set('plazo_min', $linea->plazo_minimo);
                                $set('plazo_max', $linea->plazo_maximo);
                                $set('monto_min', $linea->monto_min);
                                $set('monto_max', $linea->monto_max);
                            }
                            calcularVencimiento($get, $set);
                        })
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $linea = Aholinea::find($state);
                            if ($linea) {
                                $set('interes_anual', $linea->tasa_interes);
                                $set('plazo', $linea->plazo_minimo);
                                $set('plazo_min', $linea->plazo_minimo);
                                $set('plazo_max', $linea->plazo_maximo);
                                $set('monto_min', $linea->monto_min);
                                $set('monto_max', $linea->monto_max);
                            }
                            calcularVencimiento($get, $set);
                        })
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('tipo')
                        ->options([
                            'corriente' => 'Corriente',
                            'plazo_fijo' => 'Plazo Fijo',
                        ])
                        ->native(false)
                        ->required()
                        ->default('corriente'),
                    ToggleButtons::make('estado')
                        ->options([
                            'activa' => 'Activo',
                            'inactiva' => 'Inactivo',
                            'bloqueada' => 'Bloqueado',
                        ])
                        ->colors([
                            'activa' => 'success',
                            'inactiva' => 'danger',
                            'bloqueada' => 'warning',
                        ])
                        ->default('inactiva')
                        ->required()
                        ->inline()
                        ->visibleOn('edit'),
                    Forms\Components\DatePicker::make('fecha_apertura')
                        ->default(now())
                        ->reactive()
                        ->live(onBlur: true)
                        ->native(false)
                        ->required()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            calcularVencimiento($get, $set);
                        }),
                    Forms\Components\TextInput::make('plazo')
                        ->suffix('Meses')
                        ->required()
                        ->reactive()
                        ->integer()
                        ->live(onBlur: true)
                        ->rule(fn(callable $get) => "between:{$get('plazo_min')},{$get('plazo_max')}")
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            calcularVencimiento($get, $set);
                        }),
                    Forms\Components\DatePicker::make('fecha_vencimiento')
                        ->readOnly()
                        ->native(false)
                        ->required(),
                    Forms\Components\TextInput::make('interes_anual')
                        ->readOnly()
                        ->numeric()
                        ->suffix('%')
                        ->required(),
                    Forms\Components\TextInput::make('saldo')
                        ->label('Saldo inicial')
                        ->prefix('Q.')
                        ->numeric()
                        ->default(0)
                        ->visibleOn('edit'),
                    Forms\Components\TextInput::make('numero_renovaciones')
                        ->readOnly()
                        ->integer()
                        ->default(0)
                        ->visibleOn('edit'),

                    Forms\Components\RichEditor::make('notas')
                        ->columnSpanFull(),
                ])->columns(4)->visible(fn($get) => !empty($get('cliente_id'))),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_cuenta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state))),
                Tables\Columns\TextColumn::make('saldo')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interes_anual')
                    ->numeric()
                    ->suffix(' %')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activa' => 'success',
                        'inactiva' => 'gray',
                        'bloqueada' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('fecha_apertura')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('renovacion_automatica')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('beneficiario_count')
                    ->counts('beneficiarios')
                    ->label('No. Beneficiarios')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('beneficiarios')
                    ->label('Beneficiarios')
                    ->getStateUsing(function ($record) {
                        ($record->beneficiario) ? $datos = $record->beneficiario->pluck('nombre_completo')->join(', ') : $datos = '';
                        return $datos;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
                Tables\Actions\Action::make('aperturar')
                    ->label('Aperturar')
                    ->action(function (Ahorro $record, array $data) {
                        realizarPrimerDeposito($record, $data);
                    })
                    ->form([
                        Forms\Components\DateTimePicker::make('fecha')
                            ->readOnly()
                            ->label('Fecha')
                            ->default(now()),
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
                        Forms\Components\TextInput::make('deposito')
                            ->required()
                            ->numeric()
                            ->rule(fn($record) => "between:{$record->aholinea->monto_min},{$record->aholinea->monto_max}")
                            ->prefix('Q'),
                        Forms\Components\Textarea::make('notas')->default('Deposito inicial')->readOnly(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Registrar Primer Depósito')
                    ->modalDescription(fn(Ahorro $record) => "Esta acción depositará el monto inicial en la cuenta de **{$record->cliente->nombre_completo}**.")
                    ->modalSubmitActionLabel('Depositar')
                    ->color('success')
                    ->authorize(fn() => Gate::allows('aperturar_ahorro'))
                    ->visible(fn(Ahorro $ahorro) => $ahorro->nuevo == true)
                    ->icon('heroicon-o-lock-open'),
                ActionGroup::make([
                    // Html2MediaAction::make('Contrato')
                    //     ->content(fn($record) => view('pdf.contrato_ahorro', ['contrato' => $record]))
                    //     ->icon('heroicon-s-document')
                    //     ->preview()
                    //     ->savePdf()
                    //     ->filename(fn($record) => 'CNT_(' . $record->comprobante . ')-' . $record->cliente->nombre . '.pdf')
                    //     ->visible(fn($record) => !$record->nuevo)
                    //     ->authorize(fn() => Gate::allows('contrato_ahorro')),
                    Html2MediaAction::make('EstadoCuenta')
                        ->label('Estado de Cuenta')
                        ->content(fn($record) => view('pdf.est-cuenta_ahorro', ['ahorro' => $record, 'movimientos' => Ahmovimiento::where('ahorro_id', $record->id)->orderBy('fecha', 'asc')->get()]))
                        ->icon('heroicon-s-printer')
                        ->preview()
                        ->savePdf()
                        ->filename(fn($record) => 'EstCuenta_' . $record->numero_cuenta . '.pdf')
                        ->format('letter', 'in')
                        ->visible(fn($record) => !$record->nuevo)
                        ->authorize(fn() => Gate::allows('estadoCuenta_ahorro')),
                    Tables\Actions\Action::make('renovar')
                        ->label('Renovar')
                        ->action(function ($record, array $data) {
                            // Actualizar los valores proporcionados
                            $record->tipo = $data['tipo'];
                            $record->interes_anual = $data['interes_anual'];
                            $record->plazo = $data['plazo'];
                            $record->saldo_contrato = $data['saldo'];

                            // Renovar la cuenta
                            $record->numero_renovaciones = ($record->numero_renovaciones ?? 0) + 1;
                            $record->fecha_apertura = now();
                            $record->fecha_vencimiento = Carbon::parse($record->fecha_apertura)->addMonths((int)$data['plazo']);
                            $record->save();

                            Notification::make()
                                ->title('Cuenta renovada correctamente')
                                ->success()
                                ->send();
                        })
                        ->form([
                            Forms\Components\Select::make('tipo')
                                ->label('Tipo de Cuenta')
                                ->options([
                                    'corriente' => 'Corriente',
                                    'plazo_fijo' => 'Plazo Fijo',
                                ])
                                ->required()
                                ->native(false)
                                ->default(fn($record) => $record->tipo),
                            Forms\Components\Select::make('aholinea_id')
                                ->required()
                                ->relationship('aholinea', 'nombre')
                                ->searchable()
                                ->optionsLimit(5)
                                ->preload()
                                ->default(fn($record) => $record->aholinea_id),
                            Forms\Components\TextInput::make('interes_anual')
                                ->label('Tasa de Interés Anual')
                                ->suffix('%')
                                ->numeric()
                                ->required()
                                ->default(fn($record) => $record->interes_anual),
                            Forms\Components\TextInput::make('plazo')
                                ->label('Plazo (en meses)')
                                ->numeric()
                                ->suffix('Meses')
                                ->required()
                                ->default(fn($record) => $record->plazo),
                            Forms\Components\TextInput::make('saldo')
                                ->label('Saldo Actual')
                                ->readOnly()
                                ->numeric()
                                ->prefix('Q')
                                ->required()
                                ->default(fn($record) => $record->saldo),
                        ])
                        ->requiresConfirmation()
                        ->color('success')
                        ->authorize(fn() => Gate::allows('renovar_ahorro'))
                        ->visible(fn($record) => now()->gt(Carbon::parse($record->fecha_vencimiento)))
                        ->icon('heroicon-o-arrow-path'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AhmovimientosRelationManager::class,
            BeneficiariosRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAhorros::route('/'),
            'create' => Pages\CreateAhorro::route('/create'),
            'view' => Pages\ViewAhorro::route('/{record}'),
            'edit' => Pages\EditAhorro::route('/{record}/edit'),
        ];
    }
}



function calcularVencimiento(callable $get, callable $set)
{
    if ($fecha = $get('fecha_apertura')) {
        $fecha = Carbon::parse($fecha);
        $plazo = intval($get('plazo'));

        $fecha->addMonths($plazo);

        $set('fecha_vencimiento', $fecha->toDateString());
    }
}

function realizarPrimerDeposito(Ahorro $ahorro, array $data)
{
    $cajaActiva = Caja::where('agencia_id', $ahorro->agencia_id)
        ->where('abierta', true) // Verifica que la caja esté activa
        ->first();

    if ($cajaActiva) {
        $monto = floatval($data['deposito']);

        // Registrar el movimiento en la tabla de movimientos
        Ahmovimiento::create([
            'agencia_id'       => $ahorro->agencia_id,
            'caja_id'          => $cajaActiva->id, // Ajusta según la caja en uso
            'ahorro_id'        => $ahorro->id,
            'fecha'            => Carbon::parse($data['fecha']),
            'comprobante'      => $data['comprobante'],
            'tipo'             => $data['tipo'],
            'deposito'         => $monto,
            'monto'            => $monto,
            'saldo'            => $monto,
            'notas'            => $data['notas'],
        ]);

        // Actualizar saldo y estado de la cuenta de ahorro
        $ahorro->update([
            'saldo_contrato'  => $monto,
            'saldo'  => $monto,
            'estado' => 'activa',
            'nuevo'  => false, // Se marca como false porque ya tiene movimiento
        ]);

        // Notificación en Filament
        Notification::make()
            ->title('Depósito Exitoso')
            ->body("Se ha realizado el primer depósito de Q{$monto} en la cuenta {$ahorro->numero_cuenta}.")
            ->success()
            ->send();
    } else {
        // Enviar notificación de advertencia
        Notification::make()
            ->title('No hay caja abierta')
            ->warning()
            ->body("No hay una caja activa para {$ahorro->agencia->nombre}.")
            ->send();
    }
}
