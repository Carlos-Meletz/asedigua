<?php

namespace App\Filament\Resources\CreditoResource\RelationManagers;

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

class CrmovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'crmovimientos';

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
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('pago')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capital')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('interes')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('mora')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('otros')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldocap')
                    ->numeric()
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\IconColumn::make('anulado')
                    ->boolean(),
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
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Html2MediaAction::make('Cpr')
                    ->content(fn($record) => view('pdf.comprobante_credito', ['movimiento' => $record]))
                    ->icon('heroicon-s-printer')
                    ->preview()
                    ->savePdf()
                    ->format('letter', 'in')
                    ->margin([0.3, 0.5, 0.3, 0.5])
                    ->filename(fn($record) => 'CPR-' . $record->comprobante . '.pdf')
                    ->authorize(fn($record) => Gate::allows('comprobante_crmovimiento') || Carbon::parse($record->created_at)->diffInMinutes(now()) <= 10),
            ])
            ->bulkActions([]);
    }
}
