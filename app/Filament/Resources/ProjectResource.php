<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $modelLabel = 'Projeto';
    protected static ?string $pluralModelLabel = 'Projetos';
    protected static ?string $navigationLabel = 'Projetos';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Administração';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Projeto')
                    ->description('Detalhes básicos sobre o projeto')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Projeto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-tag')
                            ->extraInputAttributes(['class' => 'font-medium']),

                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-tag'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->rows(5)
                            ->extraInputAttributes(['class' => 'min-h-[120px]'])
                            ,
                    ]),

                Forms\Components\Section::make('Metadados')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('created_by')
                            ->label('Criado Por')
                            ->relationship('creator', 'name')
                        ->disabled()
                            ->default(Auth::id())
                            ->required()
                            ->dehydrated(True)

                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-user'),

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
                    ->description(fn (Project $record) => Str::limit($record->description, 30))
                    ->icon('heroicon-o-rocket-launch'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Criado Por')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-user-circle'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()

                    ->indicator('Categoria'),

                Tables\Filters\SelectFilter::make('created_by')
                    ->relationship('creator', 'name')
                    ->searchable()

                    ->indicator('Criado Por'),


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
                Tables\Grouping\Group::make('category.name')
                    ->label('Por Categoria')
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
            // Adicione relacionamentos se necessário
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
