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
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $modelLabel = 'Avaliação';
    protected static ?string $pluralModelLabel = 'Avaliações';
    protected static ?int $navigationSort = -1;
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
                                $component->state( Auth::id()) ;
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
                    ->helperText('Descreva se foi deferido, se foi indeferido ou motivos'),


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
                ->label('ATIVIDADE')
                ->searchable()
                ->sortable()
                ->weight('medium')
                ->description(fn ($record) => Str::limit($record->activity->description, 50))
                ->tooltip(fn ($record) => $record->activity->description)
                ->wrap()
                ->size('sm'),

            Tables\Columns\TextColumn::make('activity.user.name')
                ->label('ALUNO')
                ->searchable()
                ->sortable()
                ->description(function ($record) {
                     return  $record->activity->user->email || 'N/A';
                    })
                ->weight('medium')
                ->size('sm'),

            Tables\Columns\TextColumn::make('evaluator.name')
                ->label('AVALIADOR')
                ->searchable()
                ->sortable()
                ->description(function ($record) {
                     return $record->evaluator && $record->evaluator->email ? $record->evaluator->email : 'N/A';
                })
                ->weight('medium')
                ->size('sm')
                ->badge()
                ->color('info'),

            Tables\Columns\BadgeColumn::make('decision')
                ->label('STATUS')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'approved' => 'Aprovado',
                    'rejected' => 'Rejeitado',
                    'pending_review' => 'Pendente',
                })
                ->colors([
                    'success' => 'approved',
                    'danger' => 'rejected',
                    'warning' => 'pending_review',
                ])
                ->icon(fn (string $state): string => match ($state) {
                    'approved' => 'heroicon-o-check-circle',
                    'rejected' => 'heroicon-o-x-circle',
                    'pending_review' => 'heroicon-o-clock',
                })
                ->size('md'),

            Tables\Columns\TextColumn::make('evaluated_at')
                ->label('DATA AVALIAÇÃO')
                ->dateTime('d/m/Y H:i')
                ->since()
                ->sortable()
                ->description(function ($record) {
                    if (!$record->evaluated_at) {
                        return 'Não avaliado';
                    }

                    $evaluatedAt = is_string($record->evaluated_at)
                        ? \Carbon\Carbon::parse($record->evaluated_at)
                        : $record->evaluated_at;

                    return 'Avaliado ' . $evaluatedAt->diffForHumans();
                })
                ->color(fn ($record) => $record->evaluated_at ? 'success' : 'danger')
                ->size('sm')
                ->weight('medium'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('decision')
                ->options([
                    'approved' => 'Aprovado',
                    'rejected' => 'Rejeitado',
                    'pending_review' => 'Pendente',
                ])
                ->label('Status da Avaliação')
                ->native(false),

            Tables\Filters\Filter::make('recentes')
                ->label('Avaliações Recentes')
                ->query(fn (Builder $query): Builder => $query->where('evaluated_at', '>=', now()->subDays(7)))
                ->indicator('Últimos 7 dias'),

            Tables\Filters\Filter::make('pending')
                ->label('Pendentes de Avaliação')
                ->query(fn (Builder $query): Builder => $query->whereNull('evaluated_at'))
                ->indicator('Pendentes'),
        ])
        ->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),

                Action::make('avaliar')
                    ->label('Avaliar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->modalHeading('Avaliar Atividade')
                    ->modalDescription(fn ($record) => "Você está avaliando a atividade: {$record->activity->title}")
                    ->modalSubmitActionLabel('Salvar Avaliação')
                    ->form([
                        Forms\Components\Select::make('decision')
                            ->label('Decisão')
                            ->options([
                                'approved' => 'Aprovado',
                                'rejected' => 'Rejeitado',
                                'pending_review' => 'Revisão Pendente',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('comentario')
                            ->label('Comentário')
                            ->placeholder('Digite seu feedback detalhado...')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->rows(5),
                    ])
                    ->action(function (Evaluation $record, array $data) {
                        $record->update([
                            'decision' => $data['decision'],
                            'evaluated_at' => now(),
                            'comentario' => $data['comentario'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Avaliação registrada!')
                            ->success()
                            ->send();
                    }),
            ])
            ->tooltip('Ações')
            ->color('primary')
            ->icon('heroicon-s-cog-6-tooth')
            ->button(),
        ])
        ->defaultSort('evaluated_at', 'asc')
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),

                Tables\Actions\BulkAction::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Evaluation $records) => $records->each->update(['decision' => 'approved']))
                    ->requiresConfirmation(),

                Tables\Actions\BulkAction::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn (Evaluation $records) => $records->each->update(['decision' => 'rejected']))
                    ->requiresConfirmation(),
            ]),
        ])
        ->emptyStateHeading('Nenhuma avaliação encontrada')
        ->emptyStateDescription('Nenhum dicente requiriu nenhuma avaliação recentemente')
        ->emptyStateIcon('heroicon-o-document-magnifying-glass')
        //->emptyStateActions([
            //Tables\Actions\CreateAction::make()
            //    ->label('Criar Avaliação')
          //      ->icon('heroicon-o-plus'),
        //])
        ->deferLoading()
        ->persistSearchInSession()
        ->persistColumnSearchesInSession();
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
