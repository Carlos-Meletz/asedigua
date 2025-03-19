<?php

namespace App\Filament\Resources\CajaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

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
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')->searchable(),
                Tables\Columns\TextColumn::make('comprobante')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingreso')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('egreso')
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
