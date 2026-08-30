<?php

namespace App\Filament\Resources\WhatsAppAccounts\Pages;

use App\Filament\Resources\WhatsAppAccounts\WhatsAppAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppAccounts extends ListRecords
{
    protected static string $resource = WhatsAppAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}