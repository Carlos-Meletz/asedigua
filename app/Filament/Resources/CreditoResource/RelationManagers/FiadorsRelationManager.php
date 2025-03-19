<?php

namespace App\Filament\Resources\CreditoResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Fiador;
use App\Models\Credito;
use App\Models\Locacion;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class FiadorsRelationManager extends RelationManager
{
    protected static string $relationship = 'fiadores';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos de la persona que firmara con solicitante')->schema([
                    Select::make('anterior')
                        ->label('Firmantes anteriores del Cliente')
                        ->options(function (RelationManager $livewire) {
                            $credito = $livewire->getOwnerRecord();
                            $clienteId = $credito?->cliente_id;

                            return Fiador::whereHas('credito', function ($query) use ($clienteId, $credito) {
                                $query->where('cliente_id', $clienteId)
                                    ->where('id', '!=', $credito->id);
                            })
                                ->get()
                                ->groupBy('dpi')  // Agrupa por DPI
                                ->map(fn($group) => $group->first())
                                ->pluck('nombre_completo', 'id');
                        })
                        ->native(false)
                        ->searchable()
                        ->optionsLimit(5)
                        ->preload()
                        ->reactive()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) {
                                // Limpiar todos los campos si no se selecciona un fiador
                                $set('tipo', null);
                                $set('nombre', null);
                                $set('apellido', null);
                                $set('fecha_nacimiento', null);
                                $set('edad', null);
                                $set('estado_civil', null);
                                $set('dep_dpi', null);
                                $set('mun_dpi', null);
                                $set('dpi', null);
                                $set('relacion', null);
                                $set('profesion', null);
                                $set('direccion', null);
                                $set('telefono', null);
                                $set('firma', null);
                                // return;
                            }

                            $fiador = Fiador::find($state);
                            if ($fiador) {
                                $set('tipo', $fiador->tipo);
                                $set('nombre', $fiador->nombre);
                                $set('apellido', $fiador->apellido);
                                $set('fecha_nacimiento', $fiador->fecha_nacimiento);
                                $set('edad', $fiador->edad);
                                $set('estado_civil', $fiador->estado_civil);
                                $set('dep_dpi', $fiador->dep_dpi);
                                $set('mun_dpi', $fiador->mun_dpi);
                                $set('dpi', $fiador->dpi);
                                $set('relacion', $fiador->relacion);
                                $set('profesion', $fiador->profesion);
                                $set('direccion', $fiador->direccion);
                                $set('telefono', $fiador->telefono);
                                $set('firma', $fiador->firma);
                            }
                        }),
                    Forms\Components\TextInput::make('nombre')
                        ->required()
                        ->maxLength(70)
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('apellido')
                        ->required()
                        ->maxLength(50)
                        ->columnSpan(2),
                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            'codeudor' => 'Codeudor',
                            'fiador' => 'Fiador',
                            'testigo' => 'Testigo',
                            'garante' => 'Garante con garantía real',
                            'representante' => 'Representante Legal',
                            'conyuge' => 'Cónyuge o conviviente',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\DatePicker::make('fecha_nacimiento')
                        ->default(Carbon::now())
                        ->native(false)
                        ->required()
                        ->maxDate((Carbon::now()->year) - 16)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $edad = (Carbon::now()->year) - (Carbon::parse($state)->year);
                            $set('edad', $edad);
                        }),
                    Forms\Components\TextInput::make('edad')
                        ->integer()
                        ->required()
                        ->suffix('años.')
                        ->readOnly(),
                    Forms\Components\Select::make('estado_civil')
                        ->options([
                            'Masculino' => [
                                'soltero' => 'Soltero',
                                'casado' => 'Casado',
                                'unido' => 'Unido',
                                'divorciado' => 'Divorciado',
                                'viudo' => 'Viudo',
                            ],
                            'Femenino' => [
                                'soltera' => 'Soltera',
                                'casada' => 'Casada',
                                'unida' => 'Unida',
                                'divorciada' => 'Divorciada',
                                'viuda' => 'Viuda',
                            ],
                        ])
                        ->native(false)
                        ->required(),
                    Forms\Components\TextInput::make('relacion')
                        ->required(),
                    Forms\Components\TextInput::make('profesion')
                        ->required()->columnSpan(2),
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
                        ->afterStateUpdated(fn(callable $set) => $set('mun_dpi', null))
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
                    Forms\Components\Toggle::make('firma')
                        ->default(true),
                ])->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre_completo')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo'),
                Tables\Columns\TextColumn::make('tipo')->formatStateUsing(fn($state) => ucwords(strtolower($state))),
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
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->authorize(fn() => Gate::allows('fiadorCrear_credito')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->authorize(fn() => Gate::allows('fiadorVer_credito')),
                Tables\Actions\EditAction::make()->authorize(fn() => Gate::allows('fiadorEditar_credito')),
                Tables\Actions\DeleteAction::make()->authorize(fn() => Gate::allows('fiadorEliminar_credito')),
            ])
            ->bulkActions([]);
    }
}
