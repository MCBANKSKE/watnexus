<?php

namespace App\Filament\SuperAdmin\Resources\Users\Schemas;

use App\Models\Company;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Full Name')
                            ->maxLength(255),
                            
                        TextInput::make('email')
                            ->required()
                            ->label('Email Address')
                            ->maxLength(255)
                            ->email(),
                            
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->maxLength(255)
                            ->tel(),
                            
                        TextInput::make('password')
                            ->required()
                            ->label('Password')
                            ->maxLength(255)
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                            
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            //->multiple()
                            ->preload()
                            ->label('Roles')
                            ->hidden(fn () => !auth()->user()?->isSystemSuperAdmin() && !auth()->user()?->is_superadmin),
                            
                        Select::make('company_id')
                            ->label('Company')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                            
                        Select::make('permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->preload()
                            ->label('Permissions')
                            ->hidden(fn () => !auth()->user()?->isSystemSuperAdmin() && !auth()->user()?->is_superadmin),
                            
                        Toggle::make('is_superadmin')
                            ->label('Company Admin')
                            ->visible(fn () => auth()->user()?->isSystemSuperAdmin())
                            ->helperText('Grants authority to manage roles and permissions for all staff within this company.'),
                            
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive users cannot log in.'),
                            
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),
                            
                        DateTimePicker::make('last_login_at')
                            ->label('Last Login At'),
                            
                        TextInput::make('last_login_ip')
                            ->label('Last Login IP')
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),

               /* Section::make('Tax & Identification')
                    ->description('Tax authority and identification details')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('tax_pin')
                            ->label('Tax PIN / KRA PIN')
                            ->maxLength(255)
                            ->helperText('KRA PIN format: A001234567P'),
                            
                        Select::make('customer_type')
                            ->label('Customer Type')
                            ->options([
                                'individual' => 'Individual',
                                'business' => 'Business',
                            ])
                            ->default('individual'),
                            
                        Select::make('identification_type')
                            ->label('Identification Type')
                            ->options([
                                'national_id' => 'National ID',
                                'passport' => 'Passport',
                                'huduma' => 'Huduma Namba',
                                'business_reg' => 'Business Registration',
                                'alien_id' => 'Alien ID',
                                'other' => 'Other',
                            ])
                            ->nullable(),
                            
                        TextInput::make('identification_number')
                            ->label('Identification Number')
                            ->maxLength(255),
                    ]),*/
            ]);
    }
}