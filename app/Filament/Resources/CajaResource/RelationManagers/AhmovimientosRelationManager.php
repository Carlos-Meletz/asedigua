<?php

namespace App\Filament\Resources\CajaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AhmovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'ahmovimientos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('comprobante')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comprobante')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable('asc'),
                Tables\Columns\TextColumn::make('ahorro.cliente.nombre_completo')->searchable(),
                Tables\Columns\TextColumn::make('comprobante')
                    ->label('CPR')
                    ->numeric()->sortable(),
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
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
