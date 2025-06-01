<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Support\Enums\FontWeight;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'Categoria';  // Singular
    protected static ?string $pluralModelLabel = 'Categorias';  // Plural
    protected static ?string $navigationLabel = 'Categorias';

    public static function getNavigationGroup(): ?string
    {
        return 'Administração';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Categoria')
                    ->description('Detalhes básicos sobre a categoria')
                    ->icon('heroicon-o-tag')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome da Categoria')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-tag')
                            ->extraInputAttributes(['class' => 'font-medium']),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->rows(5)
                            ->extraInputAttributes(['class' => 'min-h-[120px]']),
                    ]),

                Forms\Components\Section::make('Metadados')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('created_by')
                            ->label('Criado Por')
                            ->relationship('creator', 'name') ->disabled()
                            ->default(Auth::id())
                            ->required()
                            ->dehydrated(True)

                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\DatePicker::make('created_at')
                            ->label('Data de Criação')

                            ->disabled()
                            ->default(now())
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
                    ->description(fn (Category $record) => Str::limit($record->description, 30))
                    ->icon('heroicon-o-tag'),

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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
