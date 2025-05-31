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
use Illuminate\Support\Facades\Auth;
class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $modelLabel = 'Avaliação';
    protected static ?string $pluralModelLabel = 'Avaliações';
    protected static ?int $navigationSort = 1;
    public static function getNavigationGroup(): ?string
{
    return 'Avaliações';
}

  public static function form(Form $form): Form
{
    return $form
        ->schema([

            // Seção Principal - Avaliação
            Forms\Components\Section::make('Dados da Avaliação')
                ->description('Informações básicas sobre a avaliação')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                     // Informações adicionais do estudante
                    Forms\Components\Section::make('Informações do Estudante')
                        ->schema([
Forms\Components\TextInput::make('registration')
    ->label('Matrícula')
    ->disabled()
    ->placeholder('-')
    ->afterStateHydrated(function ($component, $state, $record) {
        $component->state($record?->activity?->user?->registration);
    }),

Forms\Components\TextInput::make('course')
    ->label('Curso')
    ->disabled()
    ->placeholder('-')
    ->afterStateHydrated(function ($component, $state, $record) {
        $component->state($record?->activity?->user?->course);
    }),

Forms\Components\TextInput::make('paid_complementary_hours')
    ->label('Horas Complementares Concluídas')
    ->disabled()
    ->suffix('horas')
    ->placeholder('-')
    ->afterStateHydrated(function ($component, $state, $record) {
        $component->state($record?->activity?->user?->paid_complementary_hours);
    }),

                        ])
                        ->columns(3),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\Select::make('activity_id')
                                ->relationship('activity', 'title')
                                ->label('Atividade')

                                ->searchable()

                                ->required()
                                ->columnSpan(1)
                                ->prefixIcon('heroicon-o-document-text')
                                ->disabled(fn ($record) => $record !== null),


                        ])
                        ->columns(2),



                    // Novos campos de horas complementares
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('activity.request_complementary_hours')
                                ->label('Horas Solicitadas')
                                ->numeric()
                                ->suffix('horas')
                                ->disabled()
                                 ->afterStateHydrated(function ($component, $state, $record) {
        $component->state($record?->activity?->request_complementary_hours);
    })
                                ->columnSpan(1),


                                  Forms\Components\DateTimePicker::make('activity.occurrence_data')
                        ->label('Data de Realização da Atividade')
                       ->disabled()
                        ->seconds(false)
                        //->required()
                         ->afterStateHydrated(function ($component, $state, $record) {
        $component->state($record?->activity?->occurrence_data);
    })
                        ->columnSpanFull(),
                        ])
                        ->columns(2),
                ])
                ->collapsible(),


            Forms\Components\Section::make('Avaliação Completa')
    ->description('Detalhes da avaliação e feedback técnico')
    ->icon('heroicon-o-clipboard-document-check')
    ->schema([
        // Seção de Dados da Avaliação
        Forms\Components\Section::make('Dados da Avaliação')
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('evaluator_id')
                                ->relationship('evaluator', 'name')
                                ->label('Avaliador')
                                 ->afterStateHydrated(function ($component, $state, $record) {
                                $component->state(auth()->id()) ;
                            })
                        ->disabled()
                        ->dehydrated(true)
                        ->required()
                                ->required()
                                ->columnSpan(1)
                                ->prefixIcon('heroicon-o-user-circle')
                                ,
                         Forms\Components\Select::make('decision')
                                ->options([
                                    'pending_review' => 'Revisão Pendente',
                                    'approved' => 'Aprovado',
                                    'rejected' => 'Rejeitado',
                                ])
                                ->required()
                                ->label('Status da Avaliação')
                                ->native(false)
                                ->columnSpan(1)
                                ->prefixIcon('heroicon-o-check-circle'),



                        Forms\Components\DateTimePicker::make('evaluated_at')
                            ->label('Data da Avaliação')
                            ->default(now())
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ])
            ->collapsible(),

        // Seção de Feedback Técnico (agora aninhada)
        Forms\Components\Section::make('Feedback Técnico')
            ->description('Avaliação detalhada da atividade')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->relationship('feedback')
            ->schema([
                 Forms\Components\TextInput::make('validated_hours')
                                ->label('Horas Validadas')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(999)
                                ->required()
                                ->suffix('horas')
                                ->columnSpan(1),
                Forms\Components\Hidden::make('evaluation_id')
                    ->default(fn ($record) => $record?->id)
                    ->dehydrated(),

                Forms\Components\Hidden::make('activity_id')
                    ->dehydrated(),

                Forms\Components\Hidden::make('evaluator_id')
                    ->dehydrated(),

                Forms\Components\Textarea::make('comments')
                    ->label('Comentários Detalhados')
                    ->placeholder('Forneça um feedback construtivo...')
                    ->rows(6)
                    ->columnSpanFull()
                    ->extraInputAttributes(['class' => 'min-h-[150px]'])
                    ->helperText('Descreva pontos fortes, áreas de melhoria e justificativa'),


            ])
            ->afterStateHydrated(function ($set, $get, $record) {
                $set('feedback.evaluation_id', $record?->id ?? $get('id'));
                $set('feedback.activity_id', $record?->activity_id ?? $get('activity_id'));
                $set('feedback.evaluator_id', $record?->evaluator_id ?? $get('evaluator_id'));
            })
            ->collapsible(),
    ])
    ->columns(1)


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
