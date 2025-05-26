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
                Forms\Components\Select::make('activity_id')
                    ->relationship('activity', 'title')
                    ->label('Atividade')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('evaluator_id')
                    ->relationship('evaluator', 'name')
                    ->label('Avaliador')
                    //->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('decision')
                    ->options([
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        'pending_review' => 'Revisão Pendente',
                    ])
                    ->required()
                    ->label('Decisão'),

                Forms\Components\DateTimePicker::make('evaluated_at')
                    ->label('Data da Avaliação')
                    ->default(now()),
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
