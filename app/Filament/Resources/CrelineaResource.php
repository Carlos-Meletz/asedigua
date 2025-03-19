<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrelineaResource\Pages;
use App\Filament\Resources\CrelineaResource\RelationManagers;
use App\Models\Crelinea;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrelineaResource extends Resource
{
    protected static ?string $model = Crelinea::class;
    protected static ?string $navigationGroup = 'Créditos';
    protected static ?string $navigationLabel = 'Lineas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Asignacion de Lineas de Crédito')->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('tasa_interes')
                        ->required()
                        ->numeric()
                        ->suffix('% Anual'),
                    Forms\Components\TextInput::make('tasa_mora')
                        ->required()
                        ->numeric()
                        ->suffix('%'),
                    Forms\Components\TextInput::make('plazo_min')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('plazo_max')
                        ->required()
                        ->numeric(),
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
                ])->columns(4),
                Forms\Components\Repeater::make('crelineacosto')
                    ->label('Costos y Descuentos')
                    ->relationship()
                    // ->defaultItems(1)
                    ->addable(false)
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                // 'descuento' => 'Descuento',
                                'costo_administrativo' => 'Costo Administrativo',
                                // 'costo_legalizacion' => 'Costo de Legalización',
                                // 'microseguro' => 'Microseguro',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Hidden::make('es_porcentaje')
                            ->label('¿Es Porcentaje?')
                            // ->inline(false)
                            ->default(true)
                            ->reactive(),
                        Forms\Components\TextInput::make('valor')
                            ->label('Valor en Porcentaje')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('aplicacion')
                            ->label('Aplicación')
                            ->options([
                                'desembolso' => 'Desembolso',
                                // 'cuotas' => 'Cuotas',
                            ])
                            ->default('desembolso')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->grid(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tasa_interes')
                    ->numeric()
                    ->suffix('% Anual'),
                Tables\Columns\TextColumn::make('tasa_mora')
                    ->numeric()
                    ->suffix('% Mensual'),
                Tables\Columns\TextColumn::make('plazo_min')
                    ->numeric()
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plazo_max')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_min')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('monto_max')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('crelineacosto_count')
                    ->counts('crelineacosto')
                    ->label('Costos')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('costos')
                    ->label('Detalle Costos')
                    ->getStateUsing(function ($record) {
                        return $record->crelineacosto->pluck('tipo')->join(', ');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListCrelineas::route('/'),
            'create' => Pages\CreateCrelinea::route('/create'),
            'edit' => Pages\EditCrelinea::route('/{record}/edit'),
        ];
    }
}
