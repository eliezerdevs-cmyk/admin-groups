<?php

namespace App\Filament\Dashboard\Resources\Groups\Pages;

use App\Filament\Dashboard\Resources\Groups\GroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroup extends ViewRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
