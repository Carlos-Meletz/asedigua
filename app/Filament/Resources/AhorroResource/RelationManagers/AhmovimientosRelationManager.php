<?php

namespace App\Filament\Resources\AhorroResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

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
                Tables\Columns\TextColumn::make('agencia.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comprobante')
                    ->label('CPR')
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('tipo')->formatStateUsing(fn($state) => ucwords(strtolower($state)))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deposito')
                    ->numeric()
                    ->sortable()
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('retiro')
                    ->numeric()
                    ->sortable()
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('interes')
                    ->numeric()
                    ->sortable()
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('penalizacion')
                    ->numeric()
                    ->sortable()
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('saldo')
                    ->numeric()
                    ->sortable()
                    ->money('GTQ'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
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
            ->filters([])
            ->headerActions([])
            ->actions([
                Html2MediaAction::make('Cpr')
                    ->content(fn($record) => view('pdf.comprobante_ahorro', ['movimiento' => $record]))
                    ->icon('heroicon-s-printer')
                    ->preview()
                    ->savePdf()
                    ->filename(fn($record) => 'CPR_(' . $record->comprobante . ')-' . $record->ahorro->cliente->nombre . '.pdf')
                    ->authorize(fn($record) => Gate::allows('comprobante_ahmovimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10)
            ])
            ->bulkActions([]);
    }
}
