<?php

namespace App\Filament\Dashboard\Resources\Users\Schemas;

use App\Enums\GuardDay;
use App\Models\Group;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
//use Filament\Schemas\Components\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    // ── Generador de contraseña segura ────────────────────────────────────────

    public static function generatePassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers   = '0123456789';
        $symbols   = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        $password  = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        $all = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    // ── Formulario ────────────────────────────────────────────────────────────

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ── Columna izquierda: foto de perfil ─────────────────────
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto de perfil')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1', '4:3'])
                            ->directory('users/photos')
                            ->disk('private')
                            ->visibility('private')
                            ->imagePreviewHeight('320')
                            ->panelAspectRatio('1:1')
                            ->panelLayout('integrated')
                            ->uploadingMessage('Subiendo foto...')
                            ->columnSpanFull()
                            ->helperText('JPG, PNG o WEBP · Máx. 2 MB'),
                    ]),

                // ── Columna derecha: resto del formulario ─────────────────
                Section::make()
                    ->columnSpan(2)
                    ->schema([

                        // ─ Acceso al sistema ─────────────────────────────
                        Section::make('Acceso al sistema')
                            ->icon('heroicon-o-lock-closed')
                            ->collapsible()
                            ->columns(2)
                            ->schema(function () {
                                $registradoId = (string) Role::where('name', 'registrado')->value('id');

                                return [
                                    TextInput::make('name')
                                        ->label('Nombre')
                                        ->prefixIcon('heroicon-o-user')
                                        ->required(),

                                    Select::make('roles')
                                        ->label('Rol del sistema')
                                        ->multiple()
                                        ->relationship('roles', 'name', fn($query) => $query->orderBy('name'))
                                        ->searchable()
                                        ->preload()
                                        ->default([$registradoId])
                                        ->required()
                                        ->live()
                                        ->prefixIcon('heroicon-o-shield-check')
                                        ->afterStateUpdated(function (Set $set, $state) use ($registradoId) {
                                            // Limpiar credenciales si vuelve a ser solo registrado
                                            $needsCredentials = collect($state)
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty();

                                            if (! $needsCredentials) {
                                                $set('email', null);
                                                $set('password', null);
                                                $set('password_confirmation', null);
                                            }
                                        })
                                        ->hint('Define los permisos del usuario.'),

                                    TextInput::make('email')
                                        ->label('Correo electrónico')
                                        ->prefixIcon('heroicon-o-envelope')
                                        ->email()
                                        ->unique(ignoreRecord: true)
                                        ->visible(fn (Get $get): bool =>
                                            collect($get('roles'))
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty()
                                        )
                                        ->required(fn (Get $get): bool =>
                                            collect($get('roles'))
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty()
                                        ),

                                    TextInput::make('password')
                                        ->label('Contraseña')
                                        ->password()
                                        ->revealable()
                                        ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                                        ->dehydrated(fn($state) => filled($state))
                                        ->visible(fn (Get $get): bool =>
                                            collect($get('roles'))
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty()
                                        )
                                        ->required(fn (Get $get, string $operation): bool =>
                                            $operation === 'create' &&
                                            collect($get('roles'))
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty()
                                        )
                                        ->suffixActions([
                                            Action::make('generate_password')
                                                ->label('Generar')
                                                ->icon('heroicon-m-key')
                                                ->color('info')
                                                ->action(function (Set $set) {
                                                    $password = self::generatePassword();
                                                    $set('password', $password);
                                                    $set('password_confirmation', $password);
                                                }),
                                            Action::make('copy_password')
                                                ->label('Copiar')
                                                ->icon('heroicon-m-clipboard')
                                                ->color('gray')
                                                ->alpineClickHandler(
                                                    "navigator.clipboard.writeText(\$wire.data.password ?? '')
                                                        .then(() => \$tooltip('¡Copiado!', { theme: 'light' }))"
                                                ),
                                        ]),

                                    TextInput::make('password_confirmation')
                                        ->label('Confirmar contraseña')
                                        ->password()
                                        ->revealable()
                                        ->dehydrated(false)
                                        ->visible(fn (Get $get): bool =>
                                            collect($get('roles'))
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty()
                                        )
                                        ->required(fn (Get $get, string $operation): bool =>
                                            $operation === 'create' &&
                                            collect($get('roles'))
                                                ->reject(fn ($roleId) => (string) $roleId === $registradoId)
                                                ->isNotEmpty()
                                        )
                                        ->same('password'),

                                    Toggle::make('is_active')
                                        ->label('Usuario activo')
                                        ->helperText('Si se desactiva, se considera como baja en el sistema.')
                                        ->default(true)
                                        ->inline(false)
                                        ->columnSpanFull(),
                                ];
                            }),

                        // ─ Datos personales ───────────────────────────────
                        Section::make('Datos personales')
                            ->icon('heroicon-o-identification')
                            ->collapsible()
                            ->columns(3)
                            ->schema([
                                TextInput::make('last_name')
                                    ->label('Primer apellido')
                                    ->prefixIcon('heroicon-o-user'),

                                TextInput::make('second_last_name')
                                    ->label('Segundo apellido')
                                    ->prefixIcon('heroicon-o-user'),

                                DatePicker::make('birth_date')
                                    ->label('Fecha de nacimiento')
                                    ->displayFormat('d/m/Y')
                                    ->format('d/m/Y')
                                    ->prefixIcon('heroicon-o-cake')
                                    ->placeholder('dd/mm/aaaa'),

                                Select::make('gender')
                                    ->label('Género')
                                    ->options([
                                        'male'   => 'Masculino',
                                        'female' => 'Femenino',
                                        'other'  => 'Otro',
                                    ])
                                    ->placeholder('Seleccionar')
                                    ->prefixIcon('heroicon-o-user-circle')
                                    ->native(false),

                                Select::make('marital_status')
                                    ->label('Estado civil')
                                    ->options([
                                        'child' => 'Niño/a',
                                        'young' => 'Joven',
                                        'single'   => 'Soltero/a',
                                        'married_young'  => 'Casado/a Joven',
                                        'married_adult' => 'Casado/a Adulto',
                                        'married_old'  => 'Casado/a Mayor',
                                    ])
                                    ->placeholder('Seleccionar')
                                    ->prefixIcon('heroicon-o-heart')
                                    ->native(false),

                                TextInput::make('phone')
                                    ->label('Celular')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->tel(),

                                Textarea::make('address')
                                    ->label('Dirección')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),

                        // ─ Pertenencia al grupo ───────────────────────────
                        Section::make('Pertenencia al grupo')
                            ->icon('heroicon-o-user-group')
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                Select::make('groups')
                                    ->label('Grupo(s)')
                                    ->multiple()
                                    ->relationship(
                                        'groups',
                                        'name',
                                        fn($query) => $query->where('active', true)->orderBy('name')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('guard_day', null))
                                    ->prefixIcon('heroicon-o-rectangle-group')
                                    ->hint('Solo grupos activos.')
                                    ->required()
                                    ->columnSpanFull(),

                                // Solo visible si el grupo requiere día de guardia
                                Select::make('guard_day')
                                    ->label('Día de guardia')
                                    ->options(GuardDay::options())
                                    ->prefixIcon('heroicon-o-calendar-days')
                                    ->native(false)
                                    ->placeholder('Seleccionar día...')
                                    ->visible(function (Get $get): bool {
                                        $groupIds = $get('groups');

                                        if (empty($groupIds)) {
                                            return false;
                                        }

                                        return Group::whereIn('id', (array) $groupIds)
                                            ->where('requires_guard_day', true)
                                            ->exists();
                                    }),

                                DatePicker::make('join_date')
                                    ->label('Fecha de ingreso al grupo')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->displayFormat('d/m/Y')
                                    ->format('d/m/Y')
                                    ->placeholder('dd/mm/aaaa'),

                                Toggle::make('recommendation_letter')
                                    ->label('Entregó carta de recomendación')
                                    ->helperText('Marca si el usuario entregó su carta.')
                                    ->inline(false)
                                    ->columnSpanFull(),
                            ]),

                        // ─ Observaciones ──────────────────────────────────
                        Section::make('Observaciones')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->rows(4)
                                    ->placeholder('Notas adicionales sobre el usuario...')
                                    ->columnSpanFull(),
                            ]),

                        // ─ Verificación de correo (solo edición) ──────────
                        Section::make('Verificación')
                            ->icon('heroicon-o-check-badge')
                            ->collapsible()
                            ->collapsed()
                            ->visibleOn('edit')
                            ->schema([
                                DateTimePicker::make('email_verified_at')
                                    ->label('Correo verificado el')
                                    ->prefixIcon('heroicon-o-envelope-open')
                                    ->native(false),
                            ]),

                    ]),

            ]);
    }
}
