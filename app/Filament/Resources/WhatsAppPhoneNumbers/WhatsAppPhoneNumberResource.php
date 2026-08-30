<?php

namespace App\Filament\Resources\WhatsAppPhoneNumbers;

use App\Filament\Resources\WhatsAppPhoneNumbers\Pages\CreateWhatsAppPhoneNumber;
use App\Filament\Resources\WhatsAppPhoneNumbers\Pages\EditWhatsAppPhoneNumber;
use App\Filament\Resources\WhatsAppPhoneNumbers\Pages\ListWhatsAppPhoneNumbers;
use App\Filament\Resources\WhatsAppPhoneNumbers\Pages\ViewWhatsAppPhoneNumber;
use App\Filament\Resources\WhatsAppPhoneNumbers\Schemas\WhatsAppPhoneNumberForm;
use App\Filament\Resources\WhatsAppPhoneNumbers\Tables\WhatsAppPhoneNumbersTable;
use App\Models\WhatsAppPhoneNumber;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsAppPhoneNumberResource extends Resource
{
    protected static ?string $model = WhatsAppPhoneNumber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $navigationLabel = 'Phone Numbers';

    protected static string|UnitEnum|null $navigationGroup = 'WhatsApp Connection';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return WhatsAppPhoneNumberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppPhoneNumbersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsAppPhoneNumbers::route('/'),
            'create' => CreateWhatsAppPhoneNumber::route('/create'),
            'view' => ViewWhatsAppPhoneNumber::route('/{record}'),
            'edit' => EditWhatsAppPhoneNumber::route('/{record}/edit'),
        ];
    }
}