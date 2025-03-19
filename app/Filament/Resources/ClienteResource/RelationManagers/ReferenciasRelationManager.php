<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferenciasRelationManager extends RelationManager
{
    protected static string $relationship = 'referencias';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                    ->options([
                        'personal' => 'Personal',
                        'comercial' => 'Comercial',
                    ])->required(),
                Forms\Components\TextInput::make('nombre')->required(),
                Forms\Components\TextInput::make('telefono')->tel()->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('telefono'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->authorize(fn() => Gate::allows('referenciasCrear_cliente')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->authorize(fn() => Gate::allows('referenciasVer_cliente')),
                Tables\Actions\EditAction::make()
                    ->authorize(fn() => Gate::allows('referenciasEditar_cliente')),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn() => Gate::allows('referenciasEliminar_cliente')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }
}
