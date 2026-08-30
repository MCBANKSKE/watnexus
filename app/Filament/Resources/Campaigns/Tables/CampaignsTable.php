<?php

namespace App\Filament\Resources\Campaigns\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('messageTemplate.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'running',
                        'warning' => 'paused',
                        'primary' => 'completed',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_recipients')
                    ->label('Recipients')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sent_count')
                    ->label('Sent')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('delivered_count')
                    ->label('Delivered')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('read_count')
                    ->label('Read')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('failed_count')
                    ->label('Failed')
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
