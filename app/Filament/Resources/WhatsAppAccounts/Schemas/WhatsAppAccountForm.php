<?php

namespace App\Filament\Resources\WhatsAppAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Connection Method')
                    ->description('Choose how you want to connect your WhatsApp Business Account')
                    ->schema([
                        Select::make('connection_method')
                            ->label('Connection Method')
                            ->options([
                                'manual' => 'Manual (Enter credentials)',
                                'qr_code' => 'QR Code (Scan with WhatsApp mobile)',
                                'oauth' => 'OAuth (Connect with Meta)',
                            ])
                            ->default('manual')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear fields when switching methods
                                if ($state !== 'manual') {
                                    $set('business_account_id', null);
                                    $set('access_token', null);
                                    $set('token_expires_at', null);
                                }
                            }),

                        // QR Code Connection Button
                        Section::make('QR Code Connection')
                            ->description('Scan the QR code with your WhatsApp mobile app to connect instantly')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('qr_code_placeholder')
                                    ->label('QR Code')
                                    ->content(function () {
                                        return 'Click the button below to generate a QR code';
                                    })
                                    ->visible(fn ($get) => $get('connection_method') === 'qr_code'),
                            ])
                            ->footerActions([
                                Action::make('generate_qr_code')
                                    ->label('Generate QR Code')
                                    ->icon('heroicon-o-qr-code')
                                    ->action(function () {
                                        // This will be handled via Livewire in the page
                                        return redirect()->route('whatsapp.qr.generate');
                                    })
                                    ->visible(fn ($get) => $get('connection_method') === 'qr_code'),
                            ])
                            ->visible(fn ($get) => $get('connection_method') === 'qr_code'),

                        // OAuth Connection Button
                        Section::make('OAuth Connection')
                            ->description('Connect with your Meta/Facebook account')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('oauth_placeholder')
                                    ->label('OAuth')
                                    ->content(function () {
                                        return 'Click the button below to connect via Meta OAuth';
                                    })
                                    ->visible(fn ($get) => $get('connection_method') === 'oauth'),
                            ])
                            ->footerActions([
                                Action::make('connect_oauth')
                                    ->label('Connect with Meta')
                                    ->icon('heroicon-o-lock-closed')
                                    ->url(function () {
                                        try {
                                            $service = app(\App\Services\WhatsApp\Authentication\OAuthConnectService::class);
                                            return $service->getAuthorizationUrl();
                                        } catch (\Exception $e) {
                                            return null;
                                        }
                                    })
                                    ->openUrlInNewTab()
                                    ->visible(fn ($get) => $get('connection_method') === 'oauth'),
                            ])
                            ->visible(fn ($get) => $get('connection_method') === 'oauth'),
                    ]),

                Section::make('WhatsApp Business Account')
                    ->description('Connect your Meta WhatsApp Business Account. Phone numbers are synced automatically from Meta after the connection is verified.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Account name')
                            ->required(fn ($get) => $get('connection_method') === 'manual')
                            ->maxLength(255)
                            ->placeholder('e.g. Main Business Account')
                            ->columnSpan(1),

                        TextInput::make('business_account_id')
                            ->label('WhatsApp Business Account ID (WABA ID)')
                            ->required(fn ($get) => $get('connection_method') === 'manual')
                            ->maxLength(255)
                            ->placeholder('e.g. 102290129340398')
                            ->helperText('Found in Meta Business Manager under Account → WhatsApp → Account ID')
                            ->columnSpan(1)
                            ->visible(fn ($get) => $get('connection_method') === 'manual'),

                        TextInput::make('access_token')
                            ->label('Access token')
                            ->password()
                            ->revealable()
                            ->required(fn ($get, $operation) => $get('connection_method') === 'manual' && $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText(fn ($get, $operation) => $operation === 'edit'
                                ? 'Leave blank to keep the existing token.'
                                : 'System user token with whatsapp_business_messaging permission.')
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('connection_method') === 'manual'),

                        DateTimePicker::make('token_expires_at')
                            ->label('Token expires at (optional)')
                            ->native(false)
                            ->columnSpan(1)
                            ->visible(fn ($get) => $get('connection_method') === 'manual'),
                    ])
                    ->visible(fn ($get) => $get('connection_method') === 'manual'),
            ]);
    }
}