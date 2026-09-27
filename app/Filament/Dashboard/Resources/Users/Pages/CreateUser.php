<?php

namespace App\Filament\Dashboard\Resources\Users\Pages;

use App\Filament\Dashboard\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['password'])) {
            $this->plainPassword = $data['password'];
        }
        return $data;
    }

    /**
     * Hook que se ejecuta inmediatamente después de crear el registro en la BD.
     */
    protected function afterCreate(): void
    {
        // Solo enviamos el correo si se generó una contraseña
        if ($this->plainPassword !== null) {
            $user = $this->record; // $this->record es la instancia del modelo creada
            if ($user->email && $user->is_active) {
            Mail::to($user->email)->send(new WelcomeUserMail($user, $this->plainPassword));
        }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()->success()
            ->title('Registro exitoso')
            ->body('El usuario se ha creado exitosamente')
            ->duration(5000)
            ->send()
            ;
    }
}
