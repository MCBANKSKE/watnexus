<?php

namespace App\Filament\Resources\WhatsAppAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('WhatsApp Business Account')
                    ->description('Connect with a Meta system-user access token. The connection is verified and phone numbers are synced after saving.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Account name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Main Business Account')
                            ->columnSpan(1),

                        TextInput::make('business_account_id')
                            ->label('WhatsApp Business Account ID (WABA ID)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. 102290129340398')
                            ->helperText('Found in Meta Business Manager under Account → WhatsApp → Account ID')
                            ->columnSpan(1),

                        TextInput::make('access_token')
                            ->label('Access token')
                            ->password()
                            ->revealable()
                            ->required(fn ($get, $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText(fn ($get, $operation) => $operation === 'edit'
                                ? 'Leave blank to keep the existing token.'
                                : 'System user token with whatsapp_business_messaging permission.')
                            ->columnSpanFull(),

                        DateTimePicker::make('token_expires_at')
                            ->label('Token expires at (optional)')
                            ->native(false)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
