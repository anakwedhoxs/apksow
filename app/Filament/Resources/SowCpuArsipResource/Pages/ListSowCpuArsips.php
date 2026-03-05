<?php

namespace App\Filament\Resources\SowCpuArsipResource\Pages;

use App\Filament\Resources\SowCpuArsipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSowCpuArsips extends ListRecords
{
    protected static string $resource = SowCpuArsipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
