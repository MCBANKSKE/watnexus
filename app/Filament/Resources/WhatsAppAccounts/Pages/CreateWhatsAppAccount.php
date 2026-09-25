<?php

namespace App\Filament\Resources\WhatsAppAccounts\Pages;

use App\Filament\Resources\WhatsAppAccounts\WhatsAppAccountResource;
use App\Services\WhatsApp\Authentication\SyncWhatsAppPhoneNumbersService;
use App\Services\WhatsApp\Authentication\TestWhatsAppConnectionService;
use App\Models\Company;
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

    /**
     * The company is derived from the authenticated membership, never from
     * browser-supplied form input. This prevents a cross-company connection.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $company = auth()->user()?->companies()->wherePivot('is_active', true)->first();

        if (! $company instanceof Company) {
            throw new RuntimeException('An active company is required before connecting WhatsApp.');
        }

        $data['company_id'] = $company->id;
        $data['connection_method'] = 'manual';
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        $account = $this->record;

        try {
            $ok = app(TestWhatsAppConnectionService::class)->handle($account);

            if ($ok) {
                $numbers = app(SyncWhatsAppPhoneNumbersService::class)->handle($account);
                $account->update(['status' => 'connected']);
            }

            Notification::make()
                ->setTitle($ok ? 'WhatsApp connected' : 'Connection check failed')
                ->body($ok
                    ? count($numbers).' phone number(s) synced from Meta.'
                    : 'Meta rejected the request. The account remains pending; verify the WABA ID and system-user token.')
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
