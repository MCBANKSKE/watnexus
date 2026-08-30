<?php

namespace App\Filament\Resources\WhatsAppPhoneNumbers\Pages;

use App\Filament\Resources\WhatsAppPhoneNumbers\WhatsAppPhoneNumberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhatsAppPhoneNumber extends EditRecord
{
    protected static string $resource = WhatsAppPhoneNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
