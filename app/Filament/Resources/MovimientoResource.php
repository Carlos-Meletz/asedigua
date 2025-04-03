<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Helpers\Funciones;
use App\Models\Movimiento;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MovimientoResource\Pages;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use App\Filament\Resources\MovimientoResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class MovimientoResource extends Resource implements HasShieldPermissions
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
    protected static ?string $model = Movimiento::class;
    protected static ?string $navigationLabel = 'Transacciones';
    protected static ?string $navigationGroup = 'Otros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Generales')->schema([

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
                        ->required()->default(fn() => Auth::user()->empleado?->agencia_id ?? 1)
                        ->native(false),
                    Forms\Components\Hidden::make('caja_id')
                        ->required(),
                    Forms\Components\DatePicker::make('fecha')
                        ->required()
                        ->label('Fecha')
                        ->reactive()
                        ->live(onBlur: true)
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
                ])->columns(4),
                Section::make('Registro del Movimiento')->schema([
                    Forms\Components\Textarea::make('descripcion')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('ingreso')
                        ->required()
                        ->numeric()
                        ->reactive()
                        ->live(true)
                        ->prefix('Q')
                        ->default(0.00)
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('egreso')
                        ->required()
                        ->numeric()
                        ->reactive()
                        ->live(true)
                        ->prefix('Q')
                        ->default(0.00)
                        ->columnSpan(2),
                ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable('asc'),
                Tables\Columns\TextColumn::make('comprobante')
                    ->label('CPR')
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state)))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ingreso')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('egreso')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Html2MediaAction::make('Cpr')
                        ->content(fn($record) => view('pdf.comprobante_movimiento', ['movimiento' => $record]))
                        ->icon('heroicon-s-printer')
                        ->preview()
                        ->savePdf()
                        ->filename(fn($record) => 'CPR-' . $record->comprobante . '.pdf')
                        ->authorize(fn($record) => Gate::allows('comprobante_movimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10),
                    Action::make('anular')
                        ->label('Anular')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($record) {
                            $record->update([
                                'descripcion' => '-Transaccion Anulada-',
                                'ingreso' => 0,
                                'egreso' => 0,
                                'anulado' => true,
                            ]);
                            Notification::make()
                                ->title('Transaccion Anulada - ' . $record->comprobante)
                                ->warning()
                                ->send();
                        })
                        ->color('danger')
                        ->authorize(fn() => Gate::allows('anular_movimiento'))
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientos::route('/'),
            'create' => Pages\CreateMovimiento::route('/create'),
            'edit' => Pages\EditMovimiento::route('/{record}/edit'),
            'view' => Pages\ViewMovimiento::route('/{record}'),
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
