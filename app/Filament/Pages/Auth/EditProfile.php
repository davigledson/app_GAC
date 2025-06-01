<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;

class EditProfile extends BaseEditProfile
{
     protected function getLayoutData(): array
    {
        return [
            'maxWidth' => MaxWidth::SevenExtraLarge,
        ];
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações Pessoais')
                    ->icon('heroicon-o-user-circle')
                    ->iconSize(IconSize::Large)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-user'),

                       

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-envelope'),

                        TextInput::make('registration')
                            ->label('Matrícula')
                            ->disabled() // Se não deve ser editável
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-identification'),
                    ]),

                Section::make('Dados Acadêmicos')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        TextInput::make('course')
                            ->label('Curso')
                            ->maxLength(100)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-book-open'),

                        TextInput::make('initiation_period')
                            ->label('Período de Ingresso')
                            ->mask('9999.9')
                            ->placeholder('Ex: 2023.1')
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-calendar'),

                        TextInput::make('paid_complementary_hours')
                            ->label('Horas Complementares')
                            ->numeric()
                            ->disabled() // Normalmente gerenciado pelo sistema
                            ->suffix('horas')
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-clock'),
                    ]),

                Section::make('Segurança')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        $this->getPasswordFormComponent()
                            ->columnSpan(1),

                        $this->getPasswordConfirmationFormComponent()
                            ->columnSpan(1),
                    ])
            ])
            ->columns(1);
    }

    protected function getPasswordFormComponent(): TextInput
    {
        return TextInput::make('password')
            ->label('Nova Senha')
            ->password()
            ->rules(['confirmed'])
            ->autocomplete('new-password')
            ->dehydrated(fn ($state) => filled($state))
            ->revealable()
            ->columnSpan(1)
            ->prefixIcon('heroicon-o-lock-closed');
    }

    protected function getPasswordConfirmationFormComponent(): TextInput
    {
        return TextInput::make('password_confirmation')
            ->label('Confirme a Senha')
            ->password()
            ->requiredWith('password')
            ->autocomplete('new-password')
            ->revealable()
            ->columnSpan(1)
            ->prefixIcon('heroicon-o-key');
    }
}
