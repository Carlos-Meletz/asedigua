<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Caja;
use Filament\Tables;
use App\Models\Ahorro;
use Filament\Forms\Form;
use App\Helpers\Funciones;
use Filament\Tables\Table;
use App\Models\Ahmovimiento;
use Ramsey\Uuid\Type\Decimal;
use Barryvdh\DomPDF\Facade\PDF;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AhmovimientoResource\Pages;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use App\Filament\Resources\AhmovimientoResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables\Actions\ActionGroup;

class AhmovimientoResource extends Resource implements HasShieldPermissions
{
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
            'anular',
        ];
    }
    protected static ?string $model = Ahmovimiento::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Transacciones';
    protected static ?string $navigationGroup = 'Ahorros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')->schema([
                    Forms\Components\Hidden::make('caja_id')
                        ->required(),
                    Forms\Components\Select::make('ahorro_id')
                        ->required()
                        ->relationship('ahorro', 'id', fn(Builder $query) => $query->with('cliente')->where('estado', 'activa')->where('nuevo', false))
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->cliente->nombre_completo . ' | ' . $record->numero_cuenta)
                        ->searchable()
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $set('minfecha', Ahmovimiento::where('ahorro_id', $get('ahorro_id'))
                                ->latest('fecha') // Ordena por la fecha más reciente
                                ->value('fecha'));
                            Funciones::actualizarSaldoAh($get, $set);
                            Funciones::calcularInteresAh($get, $set);
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
                        ->afterStateHydrated(function ($state, callable $set) {
                            Funciones::cajaActiva($state, $set);
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            Funciones::cajaActiva($state, $set);
                        })
                        ->required()
                        ->default(fn() => Auth::user()->empleado?->agencia_id ?? 1)
                        ->native(false),
                    Forms\Components\DateTimePicker::make('fecha')
                        ->required()
                        ->label('Fecha')
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            Funciones::calcularInteresAh($get, $set);
                        })
                        ->default(now())
                        ->minDate(fn(callable $get) => $get('minfecha')),
                ])->columns(4),

                Section::make('Registro de Transacciones')->schema([
                    Group::make([
                        Select::make('tipo')
                            ->label('Tipo de Pago')
                            ->options([
                                'efectivo' => 'Efectivo',
                                'banco' => 'Deposito a Banco',
                            ])
                            ->default('efectivo')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('comprobante')
                            ->required(),
                        Forms\Components\Placeholder::make('int')
                            ->label('Interes Acumulado')
                            ->columnSpan(2)
                            ->content(fn($get) => ($get('int')) ? $get('int') : 0),
                        Forms\Components\Hidden::make('interes_acumulado')
                            ->required(),
                    ])->columns(4),
                    Group::make([
                        Forms\Components\TextInput::make('deposito')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->live(true)
                            ->prefix('Q')
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                Funciones::actualizarSaldoAh($get, $set);
                            })
                            ->default(0.00),
                        Forms\Components\TextInput::make('retiro')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->live(true)
                            ->prefix('Q')
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                Funciones::actualizarSaldoAh($get, $set);
                            })
                            ->default(0.00),
                        Forms\Components\TextInput::make('interes')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->live(true)
                            ->prefix('Q')
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                Funciones::actualizarSaldoAh($get, $set);
                                Funciones::calcularInteresAh($get, $set);
                            })
                            ->default(0.00),
                        Forms\Components\TextInput::make('penalizacion')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->live(true)
                            ->prefix('Q')
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                Funciones::actualizarSaldoAh($get, $set);
                            })
                            ->default(0.00),
                    ])->columns(4),
                    Group::make([
                        Actions::make([
                            // Html2MediaAction::make('print')
                            //     ->label('Estado de Cuenta')
                            //     ->content(fn($get) => ($get('ahorro_id')) ? view('pdf.est-cuenta_ahorro', ['ahorro' => $get('ahorro_id'), 'movimientos' => Ahmovimiento::where('ahorro_id', $get('ahorro_id'))->orderBy('fecha', 'asc')->get()]) : 0)
                            //     ->icon('heroicon-s-printer')
                            //     ->preview()
                            //     ->savePdf()
                            //     ->filename(fn($record) => 'EstCuenta_' . $record->numero_cuenta . '.pdf')
                            //     ->format('letter', 'in')
                            //     ->hidden(fn($get) => !$get('ahorro_id')),
                        ]),
                        Forms\Components\TextInput::make('saldo')
                            ->readOnly()
                            ->required()
                            ->numeric()
                            ->prefix('Q')
                            ->default(0.00)
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('notas')
                            ->columnSpan(3),
                    ])->columns(4)
                ])->visible(fn($get) => !empty($get('ahorro_id'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ahorro.cliente.nombre_completo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable('asc'),
                Tables\Columns\TextColumn::make('comprobante')
                    ->label('CPR')
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state)))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deposito')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('retiro')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interes')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('penalizacion')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
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
            ])->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                TernaryFilter::make('solo_caja_abierta')
                    ->label('Solo caja abierta')
                    ->trueLabel('Sí')
                    ->falseLabel('No')
                    ->default(true)
                    ->queries(
                        true: fn($query) => $query->whereHas('caja', fn($q) => $q->where('abierta', true)),
                        false: fn($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Html2MediaAction::make('Cpr')
                        ->content(fn($record) => view('pdf.comprobante_ahorro', ['movimiento' => $record]))
                        ->icon('heroicon-s-printer')
                        ->preview()
                        ->savePdf()
                        ->filename(fn($record) => 'CPR_(' . $record->comprobante . '.pdf')
                        ->authorize(fn($record) => Gate::allows('comprobante_ahmovimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10),
                    Tables\Actions\Action::make('anular')
                        ->label('Anular')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($record) {
                            $cuenta = Ahorro::find($record->ahorro_id);
                            $movimientosRestantes = Ahmovimiento::where('ahorro_id', $record->ahorro_id)->whereNull('deleted_at')->count();
                            if ($cuenta) {
                                if ($movimientosRestantes == 1) {
                                    $cuenta->nuevo = true;
                                }
                                $cuenta->saldo = $cuenta->saldo - $record->deposito + $record->retiro + $record->penalizacion;
                                $cuenta->interes_acumulado = $cuenta->interes_acumulado + $record->interes;
                                $cuenta->save();
                            }
                            $record->update([
                                'notas' => '-Transaccion Anulada-',
                                'deposito' => 0,
                                'retiro' => 0,
                                'interes_acumulado' => 0,
                                'penalizacion' => 0,
                                'monto' => 0,
                                'saldo' => 0,
                                'anulado' => true,
                            ]);
                            Notification::make()
                                ->title('Transaccion Anulada - ' . $record->comprobante)
                                ->warning()
                                ->send();
                        })
                        ->color('danger')
                        ->authorize(fn() => Gate::allows('anular_ahmovimiento'))
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAhmovimientos::route('/'),
            'create' => Pages\CreateAhmovimiento::route('/create'),
            'edit' => Pages\EditAhmovimiento::route('/{record}/edit'),
            'view' => Pages\ViewAhmovimiento::route('/{record}'),
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
