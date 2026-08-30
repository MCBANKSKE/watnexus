<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Tables;

use App\Models\Company;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Company Name')
                    ->icon('heroicon-o-building-office')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Contact')
                    ->description(fn ($record): string => $record->phone)
                    ->wrap()
                    // ->icon('heroicon-o-phone')
                    ->searchable(),

                TextColumn::make('country.name')
                    ->label('Country')
                    ->icon('heroicon-o-globe-americas')
                    ->searchable(),

                TextColumn::make('default_currency.name')
                    ->label('Default Currency')
                    ->getStateUsing(fn ($record) => $record?->defaultCurrency
                        ? "{$record->defaultCurrency->name} ({$record->defaultCurrency->currency} {$record->defaultCurrency->currency_symbol})"
                        : '-')
                    ->searchable(),

                TextColumn::make('baseCurrency.name')
                    ->label('Base Currency')
                    ->getStateUsing(fn ($record) => $record?->baseCurrency
                        ? "{$record->baseCurrency?->name} ({$record->baseCurrency?->currency} {$record->baseCurrency?->currency_symbol})"
                        : '-')
                    ->searchable(),

                TextColumn::make('base_to_default_rate')
                    ->label('Base → Default Rate')
                    ->getStateUsing(fn ($record) => $record->base_to_default_rate
                        ? number_format($record->base_to_default_rate, 6).' '.($record->defaultCurrency?->currency_symbol ?? '')
                        : '-')
                    ->visible(fn ($record) => $record && $record->base_currency_id !== $record->default_currency_id)
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('printCompanyDetails')
                    ->label('Print Details')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Company $record) => route('companies.details.pdf', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
