<?php

namespace App\Filament\Discente\Resources\ActivityResource\Pages;

use App\Filament\Discente\Resources\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()

                ->label('Submeter Atividade'),
        ];
    }


}
