<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('whatsappPhoneNumber.phone_number')
                    ->label('Phone Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_message')
                    ->label('Last Message')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('last_message_at')
                    ->label('Last Message At')
                    ->dateTime()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'open',
                        'danger' => 'closed',
                        'warning' => 'pending',
                    ]),
                TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('unread_count')
                    ->label('Unread')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('last_message_at', 'desc');
    }
}
