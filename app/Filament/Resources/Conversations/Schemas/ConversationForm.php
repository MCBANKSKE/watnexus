<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('whatsapp_phone_number_id')
                    ->relationship('whatsappPhoneNumber', 'phone_number')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('contact_id')
                    ->relationship('contact', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'pending' => 'Pending',
                    ])
                    ->default('open')
                    ->required(),
                Select::make('assigned_to')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Textarea::make('last_message')
                    ->label('Last Message')
                    ->rows(3)
                    ->disabled(),
                Textarea::make('metadata')
                    ->label('Metadata (JSON)')
                    ->rows(3),
            ]);
    }
}
