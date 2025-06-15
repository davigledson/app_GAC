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
use App\Models\Feedback;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class ActivityResource extends Resource
{

    public static function getEloquentQuery(): Builder
{
    $user = Auth::user();
   // dd($user);

    if ($user->role === 'student' /* ou 'aluno', conforme seu banco */) {
        return parent::getEloquentQuery()
            ->where('user_id', $user->id);
    }

    return parent::getEloquentQuery();
}
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

                        ->default(fn () =>  Auth::id())
                        //->preload()
                        ->disabled()
                        ->dehydrated(true)
                        ->required()

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
                       // ->default(now())

                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

 Forms\Components\Section::make('Avaliação e Validação')
                ->schema([
                    // Campos comuns a create/edit...

                    // Seção de feedback (só aparece quando editing)
                    self::feedbackSection(),
                ])
                ->columns(2),



            Forms\Components\Section::make('Submissão')
                ->description('Informações sobre a submissão da atividade')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Forms\Components\DateTimePicker::make('submitted_at')
                        ->label('Data de Submissão')
                        ->helperText('Data e hora da submissão')
                        ->disabled()
                        ->displayFormat('d/m/Y H:i')
                         ->default(now())
                        ->seconds(false)
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ])
        ->columns(1); // Define layout de uma coluna para as seções
}
// Método separado para organização
protected static function feedbackSection(): Forms\Components\Section
{
    return   Forms\Components\Section::make('Feedback')
                ->description('Resultado da avaliação da atividade (preenchido pelo avaliador)')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                      Forms\Components\Placeholder::make('evaluator_name')
                                        ->label('Avaliador')
                                       ->content(fn ($record) => $record->feedbacks->first()->evaluator?->name ?? 'N/A')




                                        ->columnSpan(1),


                                    Forms\Components\Placeholder::make('created_at_display')
                                        ->label('Data do Feedback')
                                        ->content(function ($record) {
        $feedback = $record->feedbacks->first();

        if (!$feedback || !$feedback->created_at) {
            return 'N/A';
        }

        // Formato completo + "há x tempo"
        return $feedback->created_at->format('d/m/Y H:i') . ' (' . $feedback->created_at->diffForHumans() . ')';
    })
                                        ->columnSpan(1),
                    Forms\Components\Grid::make()
    ->schema([
        // Horas Complementares Válidas (agora como Placeholder)
        Forms\Components\Placeholder::make('valid_hours_display')
            ->label('Horas Complementares Válidas')
            ->content(function ($record) {
                $hours = $record->valid_complementary_hours ??
                         $record->feedbacks->first()->validated_hours ??
                         0;
                return $hours > 0 ? $hours . ' horas' : 'Aguardando avaliação...';
            })
            ->helperText('Horas validadas após avaliação pelo avaliador')
            ->columnSpan(1),

        // Status da Avaliação (agora como Placeholder)
        Forms\Components\Placeholder::make('status_display')
            ->label('Status da Avaliação')
            ->content(function ($record) {
                $statusLabels = [
                    'pending' => 'Pendente',
                    'approved' => 'Aprovado',
                    'rejected' => 'Rejeitado',
                ];
                return $statusLabels[$record->status] ?? 'Indefinido';
            })
            ->helperText('Status atual da avaliação')
            ->columnSpan(1),

        // Comentários (agora como Placeholder)
        Forms\Components\Placeholder::make('feedback_comments')
            ->label('Comentários')
            ->content(function ($record) {
                return $record->feedbacks->first()->comments ?? 'Nenhum comentário fornecido';
            })
            ->extraAttributes(['class' => 'whitespace-pre-wrap']) // Mantém quebras de linha
            ->columnSpanFull()
    ])
    ->columns(2)
                                ,

                ])
                ->columns(2)
                ->collapsible()
        ->visible(fn (?Activity $record): bool => $record !== null);
}

public static function table(Table $table): Table
{
    return $table
        ->defaultSort('submitted_at', 'desc') // Ordena por data de submissão (mais recente primeiro)
        ->columns([
            Tables\Columns\TextColumn::make('title')
            ->label('Titulo')
                ->searchable()
                ->sortable()
                ->description(fn (Activity $record) => Str::limit($record->description, 30))
                ->weight(FontWeight::Bold)
                ->color(Color::Blue),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Estudante')
                ->sortable()
                ->searchable()
                ->description(fn (Activity $record) => $record->user->registration ?? 'N/A')
                ->icon('heroicon-o-user-circle'),

            Tables\Columns\TextColumn::make('category.name')
                ->sortable()
                ->label('Categoria')
                ->badge()
                ->color('gray')
                ->toggleable()
                ,

            Tables\Columns\TextColumn::make('request_complementary_hours')
                ->label('Horas')
                ->numeric()
                ->sortable()
                ->alignEnd()
                ->suffix(' hrs')
                ->color(fn (Activity $record) => $record->valid_complementary_hours > 0 ? 'success' : 'gray')
                ->description(fn (Activity $record) => $record->valid_complementary_hours > 0
                    ? "Validated: {$record->valid_complementary_hours} hrs"
                    : null),

            Tables\Columns\TextColumn::make('submitted_at')
                ->label('Data de Submissão')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->icon('heroicon-o-calendar')
                ->since()
                ->toggleable()
                ,

            Tables\Columns\BadgeColumn::make('status')
    ->label('Status')

    ->colors([
        'warning' => 'pending',
        'success' => 'approved',
        'danger' => 'rejected',
    ])
    ->sortable()
    ->formatStateUsing(fn (string $state): string => match ($state) {
        'pending' => 'Pendente',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
        default => $state,
    })
    ->icon(fn (string $state): string => match ($state) {
        'pending' => 'heroicon-o-clock',
        'approved' => 'heroicon-o-check-circle',
        'rejected' => 'heroicon-o-x-circle',
        default => 'heroicon-o-question-mark-circle',
    }),

            Tables\Columns\TextColumn::make('occurrence_data')
                ->label('Data do Evento')
                ->dateTime('d/m/Y')
                ->since()
->toggleable()

                ,
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Analise pendente',
                    'approved' => 'Aprovado',
                    'rejected' => 'Rejeitado',
                ])
                ->indicator('Status')
                ->multiple()

                ,

            Tables\Filters\SelectFilter::make('category_id')
                ->relationship('category', 'name')
                ->label('Categoria de Projeto/Evento')
                ->searchable()
                ->preload()

                ->indicator('Category'),



            Tables\Filters\Filter::make('has_hours')
                ->label('With Valid Hours')
                ->query(fn (Builder $query): Builder => $query->where('valid_complementary_hours', '>', 0))
                ->indicator('Has Valid Hours'),

            Tables\Filters\Filter::make('recent_submissions')
                ->label('Last 30 Days')
                ->query(fn (Builder $query): Builder => $query->where('submitted_at', '>=', now()->subDays(30)))
                ->indicator('Recent Submissions'),
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
        ;
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
