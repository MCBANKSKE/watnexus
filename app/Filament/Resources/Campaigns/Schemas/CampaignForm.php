<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3),
                Select::make('message_template_id')
                    ->relationship('messageTemplate', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'running' => 'Running',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('scheduled_at')
                    ->label('Scheduled At')
                    ->nullable(),
                TextInput::make('total_recipients')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                TextInput::make('queued_count')
                    ->label('Queued')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                TextInput::make('sent_count')
                    ->label('Sent')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                TextInput::make('delivered_count')
                    ->label('Delivered')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                TextInput::make('read_count')
                    ->label('Read')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                TextInput::make('failed_count')
                    ->label('Failed')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                KeyValue::make('settings')
                    ->label('Settings')
                    ->keyLabel('Setting')
                    ->valueLabel('Value'),
                Textarea::make('metadata')
                    ->label('Metadata (JSON)')
                    ->rows(3),
            ]);
    }
}
