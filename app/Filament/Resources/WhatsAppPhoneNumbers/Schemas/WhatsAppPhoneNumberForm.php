<?php

namespace App\Filament\Resources\WhatsAppPhoneNumbers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppPhoneNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Phone number')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('phone_number')->label('Number'),
                        TextEntry::make('display_name')->label('Verified name')->placeholder('-'),
                        TextEntry::make('phone_number_id')->label('Phone number ID')->copyable(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('quality_rating')->label('Quality rating')->placeholder('-'),
                        TextEntry::make('messaging_limit')->label('Messaging tier')->placeholder('-'),
                        TextEntry::make('country_code')->label('Country')->placeholder('-'),
                        TextEntry::make('whatsappAccount.name')->label('WhatsApp account'),
                    ]),
            ]);
    }
}