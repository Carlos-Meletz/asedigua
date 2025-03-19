<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AholineaResource\Pages;
use App\Filament\Resources\AholineaResource\RelationManagers;
use App\Models\Aholinea;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AholineaResource extends Resource
{
    protected static ?string $model = Aholinea::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Lineas';
    protected static ?string $navigationGroup = 'Ahorros';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informacion de Lineas de Ahorro')->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('tasa_interes')
                        ->required()
                        ->numeric()
                        ->suffix('%'),
                    Forms\Components\Hidden::make('tasa_interes_minima')
                        ->required()
                        // ->numeric()
                        ->default(0),
                    // ->suffix('%'),
                    Forms\Components\Hidden::make('tasa_penalizacion')
                        ->required()
                        // ->numeric()
                        ->default(0),
                    // ->suffix('%'),
                    Forms\Components\TextInput::make('plazo_minimo')
                        ->required()
                        ->numeric()
                        ->suffix('Meses'),
                    Forms\Components\TextInput::make('plazo_maximo')
                        ->required()
                        ->numeric()
                        ->suffix('Meses'),
                    Forms\Components\TextInput::make('monto_min')
                        ->required()
                        ->numeric()
                        ->prefix('Q'),
                    Forms\Components\TextInput::make('monto_max')
                        ->required()
                        ->numeric()
                        ->prefix('Q'),
                    Forms\Components\Toggle::make('activo')
                        ->required()
                        ->inline(false),
                    Forms\Components\RichEditor::make('condiciones')
                        ->columnSpanFull(),
                ])->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tasa_interes')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tasa_interes_minima')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tasa_penalizacion')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plazo_minimo')
                    ->numeric()
                    ->suffix(' Meses'),
                Tables\Columns\TextColumn::make('plazo_maximo')
                    ->numeric()
                    ->suffix(' Meses'),
                Tables\Columns\TextColumn::make('monto_min')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_max')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAholineas::route('/'),
            'create' => Pages\CreateAholinea::route('/create'),
            'edit' => Pages\EditAholinea::route('/{record}/edit'),
        ];
    }
}
