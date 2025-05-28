<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationResource\Pages;
use App\Filament\Resources\EvaluationResource\RelationManagers;
use App\Models\Evaluation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $modelLabel = 'Avaliação';
    protected static ?string $pluralModelLabel = 'Avaliações';

   public static function form(Form $form): Form
{
    return $form
    ->schema([
        // Seção Principal - Avaliação
        Forms\Components\Section::make('Dados da Avaliação')
            ->description('Informações básicas sobre a avaliação')
            ->icon('heroicon-o-document-text')
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('activity_id')
                            ->relationship('activity', 'title')
                            ->label('Atividade')
                            ->placeholder('Selecione uma atividade')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-list-bullet'),

                        Forms\Components\Select::make('evaluator_id')
                            ->relationship('evaluator', 'name')
                            ->label('Avaliador')
                            ->placeholder('Selecione um avaliador')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-user'),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('decision')
                            ->options([
                                'approved' => 'Aprovado',
                                'rejected' => 'Rejeitado',
                                'pending_review' => 'Revisão Pendente',
                            ])
                            ->required()
                            ->label('Decisão')
                            ->native(false)
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-check-circle'),

                        Forms\Components\DateTimePicker::make('evaluated_at')
                            ->label('Data da Avaliação')
                            ->default(now())
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1)
                            ->prefixIcon('heroicon-o-calendar'),
                    ])
                    ->columns(2),
            ])
            ->collapsible(),

        // Seção de Feedback
        Forms\Components\Section::make('Feedback da Avaliação')
            ->description('Detalhes e comentários sobre a avaliação')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->relationship('feedback')
            ->schema([
                // Campos hidden (ocultos mas essenciais)
                Forms\Components\Hidden::make('evaluation_id')
                    ->default(fn ($record) => $record?->id)
                    ->dehydrated(),

                Forms\Components\Hidden::make('activity_id')
                    ->dehydrated(),

                Forms\Components\Hidden::make('evaluator_id')
                    ->dehydrated(),

                // Campos visíveis
                Forms\Components\Textarea::make('comments')
                    ->label('Comentários')
                    ->placeholder('Insira seus comentários sobre a avaliação...')
                    ->rows(5)
                    ->columnSpanFull()
                    ->extraInputAttributes(['class' => 'min-h-[120px]']),

                Forms\Components\TextInput::make('rating')
                    ->label('Nota (1-10)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->required()
                    ->prefixIcon('heroicon-o-star')
                    ->suffix('/10')
                    ->columnSpan(1),
            ])
            ->afterStateHydrated(function ($set, $get, $record) {
                $set('feedback.evaluation_id', $record?->id ?? $get('id'));
                $set('feedback.activity_id', $record?->activity_id ?? $get('activity_id'));
                $set('feedback.evaluator_id', $record?->evaluator_id ?? $get('evaluator_id'));
            })
            ->columns(2)
            ->collapsible(),
    ]);

}
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity.title')
                    ->label('Atividade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('evaluator.name')
                    ->label('Avaliador')
                    ->searchable()

                    ->sortable(),

                Tables\Columns\BadgeColumn::make('decision')
                    ->label('Decisão')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        'pending_review' => 'Revisão Pendente',
                    })
                    ->colors([
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'warning' => 'pending_review',
                    ]),

                Tables\Columns\TextColumn::make('evaluated_at')
                    ->label('Avaliado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('decision')
                    ->options([
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        'pending_review' => 'Revisão Pendente',
                    ])
                    ->label('Decisão'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                 Action::make('avaliar')
                ->label('Avaliar')
                ->form([
                    Forms\Components\Select::make('decision')
                        ->label('Decisão')
                        ->options([
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                            'pending_review' => 'Revisão Pendente',
                        ])
                        ->required(),

                    Forms\Components\Textarea::make('comentario')
                        ->label('Comentário')
                        ->maxLength(1000),
                ])
                ->action(function (Evaluation $record, array $data) {
                    $record->update([
                        'decision' => $data['decision'],
                        'evaluated_at' => now(),
                    ]);

                    if (!empty($data['comentario'])) {
                        $record->comentario = $data['comentario'];
                        $record->save();
                    }
                })

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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluations::route('/'),
            'create' => Pages\CreateEvaluation::route('/create'),
            'edit' => Pages\EditEvaluation::route('/{record}/edit'),
        ];
    }
}
