<?php

namespace App\Filament\Resources\WhatsAppAccounts;

use App\Filament\Resources\WhatsAppAccounts\Pages\CreateWhatsAppAccount;
use App\Filament\Resources\WhatsAppAccounts\Pages\EditWhatsAppAccount;
use App\Filament\Resources\WhatsAppAccounts\Pages\ListWhatsAppAccounts;
use App\Filament\Resources\WhatsAppAccounts\Pages\ViewWhatsAppAccount;
use App\Filament\Resources\WhatsAppAccounts\RelationManagers\PhoneNumbersRelationManager;
use App\Filament\Resources\WhatsAppAccounts\Schemas\WhatsAppAccountForm;
use App\Filament\Resources\WhatsAppAccounts\Tables\WhatsAppAccountsTable;
use App\Models\WhatsAppAccount;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppAccountResource extends Resource
{
    protected static ?string $model = WhatsAppAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = 'WhatsApp Accounts';

    protected static string|UnitEnum|null $navigationGroup = 'WhatsApp Connection';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return WhatsAppAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PhoneNumbersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsAppAccounts::route('/'),
            'create' => CreateWhatsAppAccount::route('/create'),
            'view' => ViewWhatsAppAccount::route('/{record}'),
            'edit' => EditWhatsAppAccount::route('/{record}/edit'),
        ];
    }
}