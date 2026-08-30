<?php

namespace App\Filament\Resources\MessageTemplates\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class MessageTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('whatsapp_template_id')
                    ->label('WhatsApp ID')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('language')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('body')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('synced_at')
                    ->label('Synced At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
