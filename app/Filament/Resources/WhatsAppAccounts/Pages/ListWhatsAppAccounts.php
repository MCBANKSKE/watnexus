<?php

namespace App\Filament\Resources\WhatsAppAccounts\Pages;

use App\Filament\Resources\WhatsAppAccounts\WhatsAppAccountResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsAppAccounts extends ListRecords
{
    protected static string $resource = WhatsAppAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connectWhatsApp')
                ->label('Connect WhatsApp')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->url(route('whatsapp.connect')),
            CreateAction::make(),
        ];
    }
}
