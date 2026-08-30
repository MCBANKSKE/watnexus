<?php

namespace App\Filament\Resources\WhatsAppAccounts\Pages;

use App\Filament\Resources\WhatsAppAccounts\WhatsAppAccountResource;
use App\Services\WhatsApp\Authentication\SyncWhatsAppPhoneNumbersService;
use App\Services\WhatsApp\Authentication\TestWhatsAppConnectionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use RuntimeException;

class CreateWhatsAppAccount extends CreateRecord
{
    protected static string $resource = WhatsAppAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $account = $this->record;

        try {
            $ok = app(TestWhatsAppConnectionService::class)->handle($account);

            Notification::make()
                ->setTitle($ok ? 'Connection verified' : 'Connection check failed')
                ->body($ok
                    ? 'The access token works. Sync your phone numbers next.'
                    : 'Meta rejected the request. Verify the WABA ID and token, then test again.')
                ->status($ok ? 'success' : 'warning')
                ->send();
        } catch (RuntimeException $e) {
            Notification::make()
                ->setTitle('Connection check could not run')
                ->body($e->getMessage())
                ->warning()
                ->send();
        }
    }
}