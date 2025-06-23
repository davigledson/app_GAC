<?php

namespace App\Filament\Coodernador\Resources\ActivityCoorResource\Pages;

use App\Filament\Coodernador\Resources\ActivityCoorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivityCoors extends ListRecords
{
    protected static string $resource = ActivityCoorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
