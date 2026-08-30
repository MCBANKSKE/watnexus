<?php

namespace App\Filament\Resources\MessageTemplates\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;

class MessageTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('whatsapp_template_id')
                    ->label('WhatsApp Template ID')
                    ->maxLength(255),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->options([
                        'marketing' => 'Marketing',
                        'utility' => 'Utility',
                        'authentication' => 'Authentication',
                    ])
                    ->required(),
                Select::make('language')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                        'ar' => 'Arabic',
                        'hi' => 'Hindi',
                    ])
                    ->default('en')
                    ->required(),
                Select::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->rows(5),
                TextInput::make('header')
                    ->label('Header')
                    ->maxLength(255),
                TextInput::make('footer')
                    ->label('Footer')
                    ->maxLength(255),
                Textarea::make('variables')
                    ->label('Variables (JSON)')
                    ->rows(3),
                Textarea::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->rows(2)
                    ->disabled(),
                KeyValue::make('buttons')
                    ->label('Buttons')
                    ->keyLabel('Type')
                    ->valueLabel('Text'),
                Textarea::make('metadata')
                    ->label('Metadata (JSON)')
                    ->rows(3),
            ]);
    }
}
