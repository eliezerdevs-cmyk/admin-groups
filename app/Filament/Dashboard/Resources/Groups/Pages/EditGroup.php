<?php

namespace App\Filament\Dashboard\Resources\Groups\Pages;

use App\Filament\Dashboard\Resources\Groups\GroupResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['admin_user_id'])) {
            $data['admin_role_filter'] = User::find($data['admin_user_id'])
                ?->roles->first()
                ?->name;
        }

        if (! empty($data['aux_user_id'])) {
            $data['aux_role_filter'] = User::find($data['aux_user_id'])
                ?->roles->first()
                ?->name;
        }

        return $data;
    }
}
