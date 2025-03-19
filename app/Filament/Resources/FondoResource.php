<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FondoResource\Pages;
use App\Filament\Resources\FondoResource\RelationManagers;
use App\Models\Fondo;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FondoResource extends Resource
{
    protected static ?string $model = Fondo::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Empresa';
    protected static ?string $navigationLabel = 'Fondos';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Asignacion de Fondos')->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(2),
                    Forms\Components\Select::make('tipo')
                        ->options([
                            'ahorro' => 'Fondo de Ahorro',
                            'credito' => 'Fondo de Préstamo',
                            'intereses' => 'Fondo de Intereses',
                            'otro' => 'Otros Fondos',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('balance')
                        ->required()
                        ->readOnly()
                        ->numeric()
                        ->prefix('Q')
                        ->default(0.00),
                    Forms\Components\RichEditor::make('descripcion')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('activo')
                        ->required()
                        ->inline(false),
                ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('tipo')->formatStateUsing(fn($state) => ucwords(strtolower($state))),
                Tables\Columns\TextColumn::make('balance')
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
            'index' => Pages\ListFondos::route('/'),
            'create' => Pages\CreateFondo::route('/create'),
            'edit' => Pages\EditFondo::route('/{record}/edit'),
        ];
    }
}
