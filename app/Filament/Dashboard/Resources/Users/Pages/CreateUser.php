<?php

namespace App\Filament\Dashboard\Resources\Users\Pages;

use App\Filament\Dashboard\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $this->data contiene el estado raw del formulario Livewire (antes de
        // dehydrateStateUsing). $data ya viene con el password hasheado por bcrypt.
        // Leemos el texto plano directamente del estado Livewire.
        $this->plainPassword = $this->data['password'] ?? null;

        return $data;
    }


    /**
     * Hook que se ejecuta inmediatamente después de crear el registro en la BD.
     */
    protected function afterCreate(): void
    {
        $user = $this->record;

        // Solo enviamos el correo si hay contraseña y el usuario tiene email activo
        if ($this->plainPassword && $user->email && $user->is_active) {
            try {
                Mail::to($user->email)->send(new WelcomeUserMail($user, $this->plainPassword));

                Log::info('Correo de bienvenida enviado a: ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de bienvenida: ' . $e->getMessage());

                Notification::make()
                    ->warning()
                    ->title('Aviso')
                    ->body('El usuario fue creado, pero no se pudo enviar el correo de bienvenida.')
                    ->persistent()
                    ->send();
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

