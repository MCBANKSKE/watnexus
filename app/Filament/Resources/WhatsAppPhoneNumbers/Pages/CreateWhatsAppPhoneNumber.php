<?php

namespace App\Filament\Resources\WhatsAppPhoneNumbers\Pages;

use App\Filament\Resources\WhatsAppPhoneNumbers\WhatsAppPhoneNumberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsAppPhoneNumber extends CreateRecord
{
    protected static string $resource = WhatsAppPhoneNumberResource::class;
}
