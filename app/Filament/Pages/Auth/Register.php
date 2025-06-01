<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Hash;

class Register extends BaseRegister
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
                            ->columnSpanFull()
                            ->prefixIcon('heroicon-o-user'),

                        TextInput::make('email')
                            ->label('E-mail Institucional')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-envelope'),

                        TextInput::make('registration')
                            ->label('Matrícula')
                            ->required()
                            ->maxLength(50)
                            ->unique()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-identification'),
                    ]),

                Section::make('Informações Acadêmicas')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        TextInput::make('course')
                            ->label('Curso')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-book-open'),

                        TextInput::make('initiation_period')
                            ->label('Período de Ingresso')
                            ->required()
                            ->mask('9999.9')
                            ->placeholder('Ex: 2023.1')
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-calendar'),
                    ]),

                Section::make('Segurança')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required()
                            ->rules(['confirmed'])
                            ->revealable()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-lock-closed'),

                        TextInput::make('password_confirmation')
                            ->label('Confirme a Senha')
                            ->password()
                            ->required()
                            ->same('password')
                            ->revealable()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-key'),
                    ])
            ])
            ->columns(1);
    }

    protected function createUser(): mixed
    {
        return static::getUserModel()::create([
            'name' => $this->form->getState()['name'],
            'email' => $this->form->getState()['email'],
            'password' => Hash::make($this->form->getState()['password']),
            'registration' => $this->form->getState()['registration'],
            'course' => $this->form->getState()['course'],
            'initiation_period' => $this->form->getState()['initiation_period'],
            'role' => 'student', // Definindo automaticamente como student
            'paid_complementary_hours' => 0 // Valor padrão
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Conta de estudante criada com sucesso!';
    }
}
