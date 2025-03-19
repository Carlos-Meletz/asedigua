<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Caja;
use App\Models\User;
use Filament\Tables;
use App\Models\Agencia;
use Filament\Forms\Form;
use App\Models\Movimiento;
use Filament\Tables\Table;
use App\Models\Ahmovimiento;

use App\Models\Crmovimiento;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\CajaResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CajaResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\CajaResource\RelationManagers\CrmovimientosRelationManager;
use App\Filament\Resources\AhorroResource\RelationManagers\AhmovimientosRelationManager;
use App\Filament\Resources\CajaResource\RelationManagers\MovimientosRelationManager;
use Filament\Tables\Actions\ActionGroup;

class CajaResource extends Resource implements HasShieldPermissions
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
            'aperturarTodo',
        ];
    }

    protected static ?string $model = Caja::class;

    protected static ?string $navigationIcon = 'heroicon-c-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Aperturar Caja Individual')->schema([
                    Select::make('agencia_id')
                        ->label('Agencia')
                        ->relationship('agencia', 'nombre')
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->required()
                        ->native(false),
                    Forms\Components\DateTimePicker::make('fecha_apertura')
                        ->default(now())
                        ->readOnly(),
                    Forms\Components\TextInput::make('saldo')
                        ->numeric()
                        ->prefix('Q')
                        ->required()
                        ->default(0.00),
                    Forms\Components\Toggle::make('abierta')
                        ->required()
                        ->inline(false),
                ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agencia.nombre'),
                Tables\Columns\TextColumn::make('fecha_apertura')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('fecha_cierre')
                    ->date(),
                Tables\Columns\TextColumn::make('ahingresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => Ahmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('deposito'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ahegresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => Ahmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum(DB::raw('COALESCE(retiro, 0) + COALESCE(interes, 0) + COALESCE(penalizacion, 0)')))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cringresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => Crmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('pago'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cregresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => Crmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('desembolso'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('otingresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => Movimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('ingreso'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('otegresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => Movimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('egreso'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('totalingresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => (Ahmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('deposito')) + (Crmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('pago') + (Movimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('ingreso'))))
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalegresos')
                    ->numeric()
                    ->money('GTQ')
                    ->state(fn($record) => (Ahmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum(DB::raw('COALESCE(retiro, 0) + COALESCE(interes, 0) + COALESCE(penalizacion, 0)'))) + (Crmovimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('desembolso')) + (Movimiento::where('caja_id', $record->id)->whereNull('deleted_at')->sum('egreso')))
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creado_por')
                    ->formatStateUsing(fn($state) => User::find($state)->username)
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('actualizado_por')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => User::find($state)->username),
                Tables\Columns\IconColumn::make('abierta')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([

                // Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
            CrmovimientosRelationManager::class,
            MovimientosRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCajas::route('/'),
            'create' => Pages\CreateCaja::route('/create'),
            'edit' => Pages\EditCaja::route('/{record}/edit'),
            'view' => Pages\ViewCaja::route('/{record}'),
        ];
    }
}
