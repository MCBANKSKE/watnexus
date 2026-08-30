<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('wa_id')
                    ->label('WhatsApp ID')
                    ->maxLength(255),
                TextInput::make('whatsapp_name')
                    ->label('WhatsApp Name')
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'blocked' => 'Blocked',
                    ])
                    ->default('active')
                    ->required(),
                TextInput::make('country_code')
                    ->maxLength(10),
                Textarea::make('metadata')
                    ->label('Metadata (JSON)')
                    ->rows(3),
            ]);
    }
}
