<?php

namespace App\Filament\Resources\AhorroResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Locacion;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BeneficiariosRelationManager extends RelationManager
{
    protected static string $relationship = 'beneficiarios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(50)
                    ->columnSpan(2),
                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(50)
                    ->columnSpan(2),
                Forms\Components\TextInput::make('relacion')
                    ->required(),
                Forms\Components\TextInput::make('profesion')
                    ->required(),
                Forms\Components\TextInput::make('dpi')
                    ->required()
                    ->maxLength(20)
                    ->mask('9999 99999 9999')
                    ->placeholder('0000 00000 0000'),
                Forms\Components\Select::make('dep_dpi')
                    ->required()
                    ->options(Locacion::departamentos())
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn(callable $set) => $set('mun_dpi', null)) // Resetea el municipio al cambiar el departamento,
                    ->native(false),
                Forms\Components\Select::make('mun_dpi')
                    ->options(
                        fn(callable $get) =>
                        $get('dep_dpi')
                            ? Locacion::municipios($get('dep_dpi'))
                            : []
                    )
                    ->required()
                    ->native(false)
                    ->reactive(),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('direccion')
                    ->required()
                    ->columnSpanFull(),
            ])->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre_completo')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo'),
                Tables\Columns\TextColumn::make('relacion'),
                Tables\Columns\TextColumn::make('profesion')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dpi'),
                Tables\Columns\TextColumn::make('dep_dpi')
                    ->formatStateUsing(fn($state) => DB::table('locacions')->where('id', $state)->value('name'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mun_dpi')
                    ->formatStateUsing(fn($state) => DB::table('locacions')->where('id', $state)->value('name'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('direccion'),
                Tables\Columns\TextColumn::make('telefono'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()->authorize(fn() => Gate::allows('beneficiarioCrear_ahorro')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->authorize(fn() => Gate::allows('beneficiarioVer_ahorro')),
                Tables\Actions\EditAction::make()->authorize(fn() => Gate::allows('beneficiarioEditar_ahorro')),
                Tables\Actions\DeleteAction::make()->authorize(fn() => Gate::allows('beneficiarioDelete_ahorro')),
            ])
            ->bulkActions([]);
    }
}
