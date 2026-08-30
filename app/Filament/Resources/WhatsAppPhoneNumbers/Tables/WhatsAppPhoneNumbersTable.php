<?php

namespace App\Filament\Resources\WhatsAppPhoneNumbers\Tables;

use App\Models\WhatsAppPhoneNumber;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WhatsAppPhoneNumbersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone_number')
                    ->label('Number')
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('display_name')
                    ->label('Verified name')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'connected',
                        'danger' => ['disconnected', 'suspended'],
                        'warning' => 'pending',
                    ])
                    ->sortable(),

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

                TextColumn::make('whatsappAccount.name')
                    ->label('Account')
                    ->searchable(),

                TextColumn::make('conversations_count')
                    ->label('Chats')
                    ->numeric(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}