<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Schemas;

use App\Models\BankTemplate;
use App\Models\Country;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ============================================================
                // 1. BASIC INFORMATION SECTION
                // ============================================================
                self::basicInformationSection(),

                // ============================================================
                // 2. ADDRESS & LOCATION SECTION
                // ============================================================
                self::addressAndLocationSection(),

                // ============================================================
                // 3. CURRENCY SECTION
                // ============================================================
                self::currencySection(),

                // ============================================================
                // 4. BANK ACCOUNTS SECTION
                // ============================================================
                self::bankAccountsSection(),

                // ============================================================
                // 5. BRANCHES SECTION
                // ============================================================
                self::branchesSection(),

                // ============================================================
                // 6. NUMBERING PREFIXES SECTION
                // ============================================================
                self::numberingPrefixesSection(),

                // ============================================================
                // 6. FISCAL YEAR SECTION
                // ============================================================
                self::fiscalYearSection(),

                // ============================================================
                // 7. SETTINGS & STATUS SECTION
                // ============================================================
                self::settingsAndStatusSection(),

                // ============================================================
                // 8. SUBSCRIPTION SECTION
                // ============================================================
                self::subscriptionSection(),
            ]);
    }

    // ========================================================================
    // SECTION BUILDERS
    // ========================================================================

    private static function basicInformationSection(): Section
    {
        return Section::make('Basic Information')
            ->columns(2)
            ->ColumnSpanFull()
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Company Name')
                    ->maxLength(255),

                TextInput::make('registration_number')
                    ->label('Registration Number')
                    ->maxLength(255),

                TextInput::make('tax_pin')
                    ->label('Tax PIN')
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Phone')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->maxLength(255)
                    ->email(),

                TextInput::make('website')
                    ->label('Website')
                    ->maxLength(255),

                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->disk('logos')
                    ->image()
                    ->maxSize(5120)
                    ->imageEditor()
                    //->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(fn ($file, callable $get) => ($get('id') ?? 'temp') . '/logo.' . $file->getClientOriginalExtension()),

                Hidden::make('logo_source')
                    ->default('shared'),
                    //->helperText('Select which application uploaded this logo to help with path resolution'),
            ]);
    }

    private static function addressAndLocationSection(): Section
    {
        return Section::make('Address & Location')
            ->columns(2)
            ->schema([
                Select::make('country_id')
                    ->label('Country')
                    ->options(fn (): array => Country::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => self::handleCountrySelected($state, $set)),

                Textarea::make('address')
                    ->label('Address')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('default_timezone')
                    ->label('Default Timezone')
                    ->default('UTC')
                    ->maxLength(255),

                TextInput::make('default_locale')
                    ->label('Default Locale')
                    ->default('en')
                    ->maxLength(255),
            ]);
    }

    private static function currencySection(): Section
    {
        return Section::make('Currency')
            ->columns(2)
            ->schema([
                Select::make('base_currency_id')
                    ->label('Base Currency')
                    ->options(fn (): array => self::getCurrencyOptions())
                    ->getOptionLabelFromRecordUsing(fn (Country $record) => self::getCurrencyOptionLabel($record))
                    ->searchable(['name', 'currency', 'currency_symbol'])
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::handleCurrencyChanged($state, $set, $get, 'base_currency_id')),

                Select::make('default_currency_id')
                    ->label('Default Currency')
                    ->options(fn (): array => self::getCurrencyOptions())
                    ->getOptionLabelFromRecordUsing(fn (Country $record) => self::getCurrencyOptionLabel($record))
                    ->searchable(['name', 'currency', 'currency_symbol'])
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::handleCurrencyChanged($state, $set, $get, 'default_currency_id')),

                TextInput::make('base_to_default_rate')
                    ->label('Base to Default Exchange Rate')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Rate from base to default currency (e.g., 1 USD = 130 KES)'),

                TextInput::make('exchange_rate_margin')
                    ->label('Exchange Rate Margin (%)')
                    ->numeric()
                    ->default(3.00)
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->helperText('Margin percentage to apply to exchange rates (e.g., 3 for 3%)'),

                Select::make('selected_currencies')
                    ->label('Selected Currencies for Exchange')
                    ->options(fn (): array => self::getCurrencyOptions())
                    ->getOptionLabelFromRecordUsing(fn (Country $record) => self::getCurrencyOptionLabel($record))
                    ->searchable(['name', 'currency', 'currency_symbol'])
                    ->preload()
                    ->multiple()
                    ->columnSpanFull()
                    ->helperText('Select currencies to include in exchange rate table. If empty, all currencies will be included.'),
            ]);
    }

    private static function bankAccountsSection(): Section
    {
        return Section::make('Bank Accounts')
            ->description('Manage bank accounts for this company')
            ->schema([
                Repeater::make('banks')
                    ->relationship()
                    ->columns(2)
                    ->schema(self::bankRepeaterSchema())
                    ->defaultItems(1)
                    
                    ->addActionLabel('Add Bank Account'),
            ])
            ->columnSpanFull();
    }

    private static function branchesSection(): Section
    {
        return Section::make('Branches')
            ->description('Manage branches for this company. The first branch will be set as Head Office automatically.')
            ->schema([
                Repeater::make('branches')
                    ->relationship()
                    ->columns(2)
                    ->schema(self::branchRepeaterSchema())
                    ->defaultItems(1)
                    ->addActionLabel('Add Branch'),
            ])
            ->columnSpanFull();
    }

    private static function branchRepeaterSchema(): array
    {
        return [
            Section::make('Branch Details')
                ->columns(2)
                ->ColumnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Branch Name')
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'hq' => 'Head Office',
                            'branch' => 'Branch',
                            'hub' => 'Hub',
                            'depot' => 'Depot',
                            'warehouse' => 'Warehouse',
                        ])
                        ->default('branch')
                        ->required()
                        ->helperText('First branch will automatically be set as Head Office'),

                    TextInput::make('email')
                        ->label('Email')
                        ->maxLength(255)
                        ->email(),

                    TextInput::make('phone')
                        ->label('Phone')
                        ->maxLength(50),

                    Select::make('country_id')
                        ->label('Country')
                        ->options(fn (): array => Country::all()->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload(),

                    TextInput::make('timezone')
                        ->label('Timezone')
                        ->maxLength(255),

                    Textarea::make('address')
                        ->label('Address')
                        ->rows(2)
                        ->columnSpanFull(),

                    Toggle::make('is_primary')
                        ->label('Primary / HQ Branch')
                        ->default(false)
                        ->inline()
                        ->helperText('Mark as the primary/headquarters branch'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(),
                ]),
        ];
    }

    private static function numberingPrefixesSection(): Section
    {
        return Section::make('Numbering Prefixes')
            ->columns(2)
            ->schema([
                TextInput::make('invoice_prefix')
                    ->label('Invoice Prefix')
                    ->default('INV-')
                    ->maxLength(255),

                TextInput::make('quotation_prefix')
                    ->label('Quotation Prefix')
                    ->default('QTN-')
                    ->maxLength(255),

                TextInput::make('quote_request_prefix')
                    ->label('Quote Request Prefix')
                    ->default('QR-')
                    ->maxLength(255),

                TextInput::make('payment_prefix')
                    ->label('Payment Prefix')
                    ->default('PAY-')
                    ->maxLength(255),

                TextInput::make('credit_note_prefix')
                    ->label('Credit Note Prefix')
                    ->default('CN-')
                    ->maxLength(255),
            ]);
    }

    private static function fiscalYearSection(): Section
    {
        return Section::make('Fiscal Year')
            ->columns(2)
            ->schema([
                TextInput::make('fiscal_year_start')
                    ->label('Fiscal Year Start')
                    ->default('01-01')
                    ->maxLength(255),

                TextInput::make('fiscal_year_end')
                    ->label('Fiscal Year End')
                    ->default('12-31')
                    ->maxLength(255),
            ]);
    }

    private static function settingsAndStatusSection(): Section
    {
        return Section::make('Settings & Status')
            ->schema([
                Select::make('business_type')
                    ->label('Business Type')
                    ->options([
                        'goods_only' => 'Goods Only (Physical Products)',
                        'services_only' => 'Services Only',
                        'both' => 'Both Goods & Services',
                    ])
                    ->default('both')
                    ->required()
                    ->helperText('Select what type of business you deal with'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    private static function subscriptionSection(): Section
    {
        return Section::make('Subscription')
            ->columns(2)
            ->schema([
                DateTimePicker::make('trial_ends_at')
                    ->label('Trial Ends At'),

                DateTimePicker::make('subscribed_at')
                    ->label('Subscribed At'),
            ]);
    }

    // ========================================================================
    // REPEATER SCHEMAS
    // ========================================================================

    private static function bankRepeaterSchema(): array
    {
        return [
            Section::make('Bank Template Selection')
                ->description('Select from pre-configured Kenyan banks with M-Pesa paybill numbers, or enter manually')
                ->schema([
                    Select::make('bank_template_id')
                        ->label('Select Bank Template')
                        ->options(fn (): array => BankTemplate::verified()->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => self::handleBankTemplateSelected($state, $set))
                        ->helperText('Kenyan banks with verified M-Pesa paybill numbers')
                        ->dehydrated(fn () => null), // Don't save this field
                ])
                ->collapsible()
                ->columnSpanFull(),

            Section::make('Bank Information')
                ->columns(2)
                ->ColumnSpanFull()
                ->schema([
                    TextInput::make('bank_name')
                        ->label('Bank Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('account_name')
                        ->label('Account Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('account_number')
                        ->label('Account Number')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('branch')
                        ->label('Branch')
                        ->maxLength(255),

                    TextInput::make('swift_code')
                        ->label('SWIFT/BIC Code')
                        ->maxLength(11)
                        ->helperText('Standard SWIFT code (8 or 11 characters)'),

                    TextInput::make('currency_code')
                        ->label('Bank Currency')
                        ->maxLength(3)
                        ->helperText('e.g. KES, USD, EUR'),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('paybill_number')
                                ->label('M-Pesa Paybill Number')
                                ->maxLength(6)
                                ->numeric()
                                ->helperText('M-Pesa paybill number (Kenya only)')
                                ->columnSpan(1),

                            Toggle::make('is_primary')
                                ->label('Primary Bank Account')
                                ->helperText('Mark as the default bank account for this company')
                                ->inline()
                                ->default(false)
                                ->columnSpan(1),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }

    // ========================================================================
    // EVENT HANDLERS
    // ========================================================================

    private static function handleCountrySelected($state, callable $set): void
    {
        if (! $state) {
            return;
        }

        $country = Country::find($state);
        if (! $country) {
            return;
        }

        // Set timezone from country data
        if (! empty($country->timezones) && is_array($country->timezones) && isset($country->timezones[0]['zoneName'])) {
            $set('default_timezone', $country->timezones[0]['zoneName']);
        }

        // Set currency from country
        $set('base_currency_id', $state);
        $set('default_currency_id', $state);
    }

    private static function handleCurrencyChanged($state, callable $set, callable $get, string $field): void
    {
        $baseCurrencyId = $get('base_currency_id');
        $defaultCurrencyId = $get('default_currency_id');

        // Reset exchange rate if currencies are different
        if ($state && $state !== ($field === 'base_currency_id' ? $defaultCurrencyId : $baseCurrencyId)) {
            $set('base_to_default_rate', null);
        }
    }

    private static function handleBankTemplateSelected($state, callable $set): void
    {
        if (! $state) {
            return;
        }

        $template = BankTemplate::find($state);
        if ($template) {
            $set('bank_name', $template->name);
            $set('paybill_number', $template->paybill_number);
        }
    }

    // ========================================================================
    // HELPER FUNCTIONS
    // ========================================================================

    private static function getCurrencyOptions(): array
    {
        return Country::all()
            ->mapWithKeys(fn ($c) => [$c->id => "{$c->name} ({$c->currency})"])
            ->toArray();
    }

    private static function getCurrencyOptionLabel(Country $record): string
    {
        return "{$record->name} ({$record->currency} - {$record->currency_symbol})";
    }
}