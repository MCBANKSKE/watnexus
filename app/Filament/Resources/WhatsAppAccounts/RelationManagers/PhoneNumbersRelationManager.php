<?php

namespace App\Filament\Resources\WhatsAppAccounts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhoneNumbersRelationManager extends RelationManager
{
    protected static string $relationship = 'phoneNumbers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone_number')
                    ->label('Number')
                    ->icon('heroicon-o-phone')
                    ->copyable(),

                TextColumn::make('display_name')
                    ->label('Verified name')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'connected',
                        'danger' => ['disconnected', 'suspended'],
                        'warning' => 'pending',
                    ]),

                TextColumn::make('quality_rating')
                    ->label('Quality')
                    ->badge()
                    ->colors([
                        'success' => 'green',
                        'warning' => ['yellow', 'orange'],
                        'danger' => 'red',
                    ])
                    ->placeholder('-'),

                TextColumn::make('messaging_limit')
                    ->label('Tier')
                    ->placeholder('-'),
            ])
            ->recordActions([]);
    }
}