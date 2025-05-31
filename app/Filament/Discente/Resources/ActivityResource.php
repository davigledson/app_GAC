<?php

namespace App\Filament\Discente\Resources;

use App\Filament\Discente\Resources\ActivityResource\Pages;
use App\Filament\Discente\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
       protected static ?string $modelLabel = 'Atividade';  // Singular
    protected static ?string $pluralModelLabel = 'Atividades';  // Plural
    protected static ?string $navigationLabel = 'Atividades';

    protected static ?int $navigationSort = 2;
    public static function getNavigationGroup(): ?string
    {
        return 'Atividades';
    }


public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Informações da Atividade')
                ->description('Preencha os dados básicos da atividade')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Estudante')
                        ->placeholder('Selecione um estudante')
                        ->default(fn () => auth()->id())
                        //->preload()
                        ->disabled()
                        ->dehydrated(true)
                        ->required()
                        ->helperText('Busque pelo nome do estudante')
                        ->columnSpan(1),

                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->label('Categoria')
                        ->placeholder('Selecione uma categoria')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Escolha a categoria da atividade')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->placeholder('Digite o título da atividade')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Máximo de 255 caracteres')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->label('Descrição')
                        ->placeholder('Descreva a atividade em detalhes...')
                        ->rows(4)
                        ->helperText('Forneça uma descrição detalhada da atividade')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Horas Complementares')
                ->description('Informações sobre horas complementares solicitadas')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\TextInput::make('request_complementary_hours')
                        ->label('Horas Complementares Solicitadas')
                        ->placeholder('Ex: 20')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(500)
                        ->required()
                        ->helperText('Quantidade de horas complementares solicitadas')
                        ->suffix('horas')
                        ->columnSpan(1),



                    Forms\Components\DateTimePicker::make('occurrence_data')
                        ->label('Data de Ocorrência')
                        ->helperText('Data em que a atividade foi realizada')
                        ->displayFormat('d/m/Y H:i')
                        ->default(now())

                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Feedback do Avaliador')
                ->description('Feedback detalhado fornecido pelo avaliador')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    Forms\Components\Placeholder::make('feedback_info')
                        ->label('')
                        ->content(function ($record) {
                            if (!$record || !$record->feedbacks()->exists()) {
                                return '📝 Aguardando feedback do avaliador...';
                            }
                            return '';
                        })
                        ->visible(fn ($record) => !$record || !$record->feedbacks()->exists()),

                    Forms\Components\Repeater::make('feedbacks')
                        ->label('')
                        ->relationship('feedbacks')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Placeholder::make('evaluator_name')
                                        ->label('Avaliador')
                                        ->content(fn ($record) => $record?->evaluator?->name ?? 'N/A')
                                        ->columnSpan(1),



                                    Forms\Components\Placeholder::make('created_at_display')
                                        ->label('Data do Feedback')
                                        ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i') ?? 'N/A')
                                        ->columnSpan(1),
                                ]),

                            Forms\Components\Textarea::make('comments')
                                ->label('Comentários')
                                ->disabled()
                                ->rows(4)
                                ->placeholder('Nenhum comentário fornecido')
                                ->columnSpanFull(),

                            Forms\Components\Section::make('Detalhes do Feedback')
                                ->schema([
                                    Forms\Components\TextInput::make('rating')
                                        ->label('Nota (1-10)')
                                        ->disabled()
                                        ->numeric()
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->collapsed(),
                        ])
                        ->disabled()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->visible(fn ($record) => $record && $record->feedbacks()->exists())
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->visible(fn ($record) => $record !== null), // Só mostra quando editando (não no criar)

            Forms\Components\Section::make('Avaliação e Validação')
                ->description('Resultado da avaliação da atividade (preenchido pelo avaliador)')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    Forms\Components\TextInput::make('valid_complementary_hours')
                        ->label('Horas Complementares Válidas')
                        ->placeholder('Aguardando avaliação...')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Horas validadas após avaliação pelo avaliador')
                        ->suffix('horas')
                        ->disabled() // Sempre desabilitado para visualização
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Status da Avaliação')
                        ->options([
                            'pending' => 'Pendente',
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                        ])
                        ->default('pending')
                        ->helperText('Status atual da avaliação')
                        ->native(false)
                        ->disabled() // Sempre desabilitado para visualização
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('evaluation_notes')
                        ->label('Observações da Avaliação')
                        ->placeholder('Nenhuma observação ainda...')
                        ->rows(3)
                        ->helperText('Comentários e observações do avaliador')
                        ->disabled() // Sempre desabilitado para visualização
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Submissão')
                ->description('Informações sobre a submissão da atividade')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Forms\Components\DateTimePicker::make('submitted_at')
                        ->label('Data de Submissão')
                        ->helperText('Data e hora da submissão')
                        ->displayFormat('d/m/Y H:i')
                        ->seconds(false)
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ])
        ->columns(1); // Define layout de uma coluna para as seções
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Student'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
