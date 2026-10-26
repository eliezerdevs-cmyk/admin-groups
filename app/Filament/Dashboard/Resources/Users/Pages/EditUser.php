<?php

namespace App\Filament\Dashboard\Resources\Users\Pages;

use App\Filament\Dashboard\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['activityUsers'])) {
            $guardDay = null;
            foreach ($data['activityUsers'] as $item) {
                if (! empty($item['guard_day'])) {
                    $guardDay = $item['guard_day'];
                    break;
                }
            }
            $data['guard_day'] = $guardDay ?? ($data['guard_day'] ?? null);
        }

        return $data;
    }
}
