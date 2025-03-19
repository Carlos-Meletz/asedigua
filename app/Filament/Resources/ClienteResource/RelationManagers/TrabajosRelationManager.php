<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class TrabajosRelationManager extends RelationManager
{
    protected static string $relationship = 'trabajos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('empresa')->required(),
                Forms\Components\TextInput::make('cargo')->required(),
                Forms\Components\TextInput::make('ingreso_mensual')->numeric()->required()->prefix('Q'),
                Forms\Components\TextInput::make('antiguedad')->label('Antigüedad (Años)')->numeric()->required(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('empresa')
            ->columns([
                Tables\Columns\TextColumn::make('empresa'),
                Tables\Columns\TextColumn::make('cargo'),
                Tables\Columns\TextColumn::make('ingreso_mensual')->money('GTQ'),
                Tables\Columns\TextColumn::make('antiguedad')->suffix(' años.'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->authorize(fn() => Gate::allows('trabajosCrear_cliente')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize(fn() => Gate::allows('trabajosVer_cliente')),
                Tables\Actions\ViewAction::make()
                    ->authorize(fn() => Gate::allows('trabajosEditar_cliente')),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn() => Gate::allows('trabajosEliminar_cliente')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }
}
