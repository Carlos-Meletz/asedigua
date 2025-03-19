<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Empleado;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmpleadoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmpleadoResource\RelationManagers;
use Filament\Forms\Components\Section;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    // protected static ?string $navigationIcon = 'heroicon-c-clipboard-document-list';
    protected static ?string $navigationLabel = 'Empleados';
    protected static ?string $navigationGroup = 'Empresa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Nuevo empleado con datos previamente registrado.')->schema([
                    Select::make('agencia_id')
                        ->label('Agencia')
                        ->relationship('agencia', 'nombre')
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->required()
                        ->native(false),

                    Select::make('cliente_id')
                        ->label('Persona')
                        ->relationship('cliente', 'nombre_completo')
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('cargo')
                        ->required()
                        ->options([
                            'Dirección y Administración' => [
                                'gerente_general' => 'Gerente General',
                                'gerente_financiero' => 'Gerente Financiero',
                                'gerente_creditos_cobros' => 'Gerente de Créditos y Cobros',
                                'gerente_riesgos' => 'Gerente de Riesgos',
                            ],
                            'Área de Créditos' => [
                                'analista_creditos' => 'Analista de Créditos',
                                'oficial_creditos' => 'Oficial de Créditos',
                                'supervisor_creditos' => 'Supervisor de Créditos',
                            ],
                            'Área de Cobros y Recuperación' => [
                                'gestor_cobros' => 'Gestor de Cobros',
                                'ejecutivo_recuperacion' => 'Ejecutivo de Recuperación',
                                'abogado_cobros' => 'Abogado de Cobros',
                            ],
                            'Área de Operaciones y Atención al Cliente' => [
                                'cajero' => 'Cajero',
                                'ejecutivo_servicio_cliente' => 'Ejecutivo de Servicio al Cliente',
                                'asesor_financiero' => 'Asesor Financiero',
                            ],
                            'Área Tecnológica y Soporte' => [
                                'administrador_sistemas' => 'Administrador de Sistemas',
                                'analista_datos' => 'Analista de Datos',
                                'soporte_tecnico' => 'Soporte Técnico',
                            ],
                            'Área de Auditoría y Cumplimient' => [
                                'auditor_interno' => 'Auditor Interno',
                                'oficial_cumplimiento' => 'Oficial de Cumplimiento',
                            ]
                        ])
                        ->native(false),
                    Forms\Components\TextInput::make('salario')
                        ->required()
                        ->numeric(),
                    Forms\Components\DatePicker::make('fecha_ingreso')
                        ->required()
                        ->default(now()),
                    Forms\Components\DatePicker::make('fecha_salida'),
                    Forms\Components\Toggle::make('estado')
                        ->label('Activo')
                        ->inline(false)
                        ->visibleOn('edit'),
                ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agencia.nombre'),
                Tables\Columns\TextColumn::make('cliente.nombre_completo')
                    ->sortable()
                    ->label('Persona'),
                Tables\Columns\TextColumn::make('cargo')
                    ->formatStateUsing(fn($state) => ucwords(strtolower($state)))
                    ->searchable(),
                Tables\Columns\TextColumn::make('salario')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_ingreso')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_salida')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('estado')
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
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit' => Pages\EditEmpleado::route('/{record}/edit'),
        ];
    }
}
