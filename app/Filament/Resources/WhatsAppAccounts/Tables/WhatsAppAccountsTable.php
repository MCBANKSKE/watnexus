<?php

namespace App\Filament\Resources\WhatsAppAccounts\Tables;

use App\Models\WhatsAppAccount;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('phoneNumbers'))
            ->columns([
                TextColumn::make('name')
                    ->label('Account')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('business_account_id')
                    ->label('WABA ID')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'connected',
                        'danger' => ['disconnected', 'suspended'],
                        'warning' => 'pending',
                    ])
                    ->sortable(),

                TextColumn::make('phone_numbers_count')
                    ->label('Numbers')
                    ->numeric(),

                TextColumn::make('token_expires_at')
                    ->label('Token expires')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : ($state && $state->diffInDays(now()) < 14 ? 'warning' : 'success'))
                    ->placeholder('Never'),

                TextColumn::make('created_at')
                    ->label('Connected')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('testConnection')
                    ->label('Test')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (WhatsAppAccount $record) {
                        $ok = app(\App\Services\WhatsApp\Authentication\TestWhatsAppConnectionService::class)->handle($record);

                        \Filament\Notifications\Notification::make()
                            ->setTitle($ok ? 'Connection is working' : 'Connection failed')
                            ->body($ok
                                ? 'The stored access token is valid.'
                                : 'Meta rejected the request. Check the WABA ID and access token.')
                            ->status($ok ? 'success' : 'danger')
                            ->send();
                    }),

                Action::make('syncPhoneNumbers')
                    ->label('Sync numbers')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (WhatsAppAccount $record) {
                        try {
                            $numbers = app(\App\Services\WhatsApp\Authentication\SyncWhatsAppPhoneNumbersService::class)->handle($record);

                            \Filament\Notifications\Notification::make()
                                ->setTitle(count($numbers).' phone number(s) synced')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            \Filament\Notifications\Notification::make()
                                ->setTitle('Sync failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('disconnect')
                    ->label('Disconnect')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (WhatsAppAccount $record): bool => $record->isConnected())
                    ->action(function (WhatsAppAccount $record) {
                        app(\App\Services\WhatsApp\Authentication\ConnectWhatsAppService::class)->disconnect($record);

                        \Filament\Notifications\Notification::make()
                            ->setTitle('Account disconnected')
                            ->warning()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}