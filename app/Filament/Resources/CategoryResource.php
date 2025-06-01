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
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Textarea::make('description'),
            Forms\Components\Select::make('created_by')
                ->relationship('creator', 'name')
                ->searchable()
                ->preload()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('description')
                ->label('Description')
                ->limit(50)
                ->wrap()
                ->toggleable()
                ->sortable(),

            Tables\Columns\TextColumn::make('creator.name')
                ->label('Created By')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ])
        ->filters([

        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
