<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Administração';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Usuário')
                    ->description('Dados básicos do usuário')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-user')
                            ->extraInputAttributes(['class' => 'font-medium']),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-envelope'),

                        Forms\Components\TextInput::make('registration')
                            ->label('Matrícula')
                            ->maxLength(50)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-identification'),

                        Forms\Components\Select::make('role')
                            ->label('Tipo de Usuário')
                            ->options([
                                'student' => 'Aluno',
                                'coordinator' => 'Coordenador',
                                'admin' => 'Administrador'
                            ])
                            ->default('student')
                            ->required()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-shield-check'),

                        Forms\Components\TextInput::make('course')
                            ->label('Curso')
                            ->maxLength(100)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-academic-cap'),

                        Forms\Components\TextInput::make('paid_complementary_hours')
                            ->label('Horas Complementares Pagas')
                            ->numeric()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-clock'),


                    ]),

                Forms\Components\Section::make('Metadados')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('email_verified_at')
                            ->label('E-mail Verificado')
                            ->columnSpan(1)
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state) {
                                $component->state((bool) $state);
                            })
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null)
                            ,

                        Forms\Components\DatePicker::make('created_at')
                            ->label('Data de Criação')
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(True)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-calendar'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-o-user-circle'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'info',
                        'coordinator'=> 'success',
                        'student' => 'warning',
                       // default => '',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'student' => 'Aluno',
                        'coordinator'=> 'Coordenador',
                        //'teacher' => 'Professor',
                        'admin' => 'Admin',
                    }),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Tipo de Usuário')
                    ->options([
                        'student' => 'Aluno',
                        'teacher' => 'Professor',
                        'admin' => 'Administrador'
                    ])
                    ->indicator('Tipo'),


            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->icon(null),
                    Tables\Actions\EditAction::make()->icon(null),
                    Tables\Actions\DeleteAction::make()->icon(null),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->groups([
                Tables\Grouping\Group::make('role')
                    ->label('Por Tipo')
                    ->collapsible(),

                Tables\Grouping\Group::make('created_at')
                    ->label('Por Mês')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relacionamentos podem ser adicionados aqui
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
