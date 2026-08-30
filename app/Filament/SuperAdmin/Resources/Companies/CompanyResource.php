<?php

namespace App\Filament\SuperAdmin\Resources\Companies;

use App\Filament\SuperAdmin\Resources\Companies\Pages\CreateCompany;
use App\Filament\SuperAdmin\Resources\Companies\Pages\EditCompany;
use App\Filament\SuperAdmin\Resources\Companies\Pages\ListCompanies;
use App\Filament\SuperAdmin\Resources\Companies\Schemas\CompanyForm;
use App\Filament\SuperAdmin\Resources\Companies\Tables\CompaniesTable;
use App\Models\Company;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Company Management';

    protected static ?string $navigationLabel = 'Companies';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
