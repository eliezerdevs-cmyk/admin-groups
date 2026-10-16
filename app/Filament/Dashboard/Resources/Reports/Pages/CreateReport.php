<?php

namespace App\Filament\Dashboard\Resources\Reports\Pages;

use App\Filament\Dashboard\Resources\Reports\ReportResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

     /**
     * Inyecta el usuario autenticado antes de guardar.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();

        return $data;
    }
}
