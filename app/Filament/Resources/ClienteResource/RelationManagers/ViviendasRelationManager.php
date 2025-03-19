<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ViviendasRelationManager extends RelationManager
{
    protected static string $relationship = 'viviendas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section::make('Datos de Vivienda')->schema([
                Forms\Components\Select::make('tipo')
                    ->options([
                        'propia' => 'Propia',
                        'alquilada' => 'Alquilada',
                        'familiar' => 'Familiar',
                        'hipotecada' => 'Hipotecada',
                    ])->required()
                    ->native(false),
                Forms\Components\TextInput::make('direccion')
                    ->required(),
                Forms\Components\Select::make('condiciones_vivienda')
                    ->options([
                        'Buena' => 'Buena',
                        'Regular' => 'Regular',
                        'Deteriorada' => 'Deteriorada',
                    ])
                    ->label('Condiciones')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('tiempo_residencia')
                    ->numeric()
                    ->label('Tiempo de Residencia (años)'),
                Forms\Components\TextInput::make('valor_estimado')
                    ->numeric()
                    ->prefix('Q')
                    ->label('Valor Estimado'),
                Forms\Components\TextInput::make('monto_alquiler')
                    ->numeric()
                    ->prefix('Q')
                    ->label('Monto de Alquiler'),
                Forms\Components\TextInput::make('nombre_propietario')
                    ->label('Propietario o Arrendador'),
                Forms\Components\Textarea::make('referencia_ubicacion')
                    ->label('Referencia o Punto Cercano'),
                Section::make('Servicios Disponibles')
                    ->schema([
                        Forms\Components\Checkbox::make('servicio_agua'),
                        Forms\Components\Checkbox::make('servicio_energia'),
                        Forms\Components\Checkbox::make('servicio_alcantarillado'),
                        Forms\Components\Checkbox::make('servicio_internet'),
                        Forms\Components\Checkbox::make('servicio_telefono'),
                    ])->columns(2)
                // ])
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('direccion')
            ->columns([
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('direccion'),
                Tables\Columns\TextColumn::make('condiciones_vivienda'),
                Tables\Columns\TextColumn::make('valor_estimado')
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('nombre_propietario'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->authorize(fn() => Gate::allows('viviendasCrear_cliente')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize(fn() => Gate::allows('viviendasVer_cliente')),
                Tables\Actions\ViewAction::make()
                    ->authorize(fn() => Gate::allows('viviendasEditar_cliente')),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn() => Gate::allows('viviendasEliminar_cliente')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }
}
