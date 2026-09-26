<?php

namespace App\Filament\Dashboard\Resources\Users\Widgets;

use App\Enums\GuardDay;
use App\Models\Group;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

//use Filament\Schemas\Components\Actions\Action;

class UserForm
{
    // ─────────────────────────────────────────────────────────────────────────
    // ⚙️  CONFIGURACIÓN DE VISIBILIDAD POR ROL
    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Campos que se OCULTAN para cada rol.
     *
     * - Si el usuario tiene varios roles, se oculta el campo si CUALQUIERA
     *   de esos roles lo tiene en su lista (unión de ocultamientos).
     * - Los roles que no estén en este array no ocultan ningún campo.
     *
     * Roles de ejemplo ya configurados. Edita/agrega los que necesites:
     */
    public const CAMPOS_OCULTOS_POR_ROL = [
        'registrado' => [
            'email',
            'password',
            'password_confirmation',
        ],
        'EHP' => [
            'marital_status',
            'birth_date',
            'recommendation_letter',
            'gender',
            'phone',
            'address',
            'join_date',
            'observations',
        ],
        'staff' => [
            'marital_status',
            'birth_date',
            'recommendation_letter',
            'gender',
            'phone',
            'address',
            'join_date',
            'observations',
        ],
        // 'admin' => [
        //     'marital_status',
        //     'birth_date',
        //     'recommendation_letter',
        // ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Generador de contraseña segura
    // ─────────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers de visibilidad por rol
    // ─────────────────────────────────────────────────────────────────────────

    /** Caché del mapa id → nombre de rol durante la request. */
    protected static ?array $roleIdToNameMap = null;

    /**
     * Devuelve un mapa [id(string) => nombre] de todos los roles.
     *
     * @return array<string, string>
     */
    protected static function getRoleIdToNameMap(): array
    {
        if (self::$roleIdToNameMap === null) {
            self::$roleIdToNameMap = Role::query()
                ->pluck('name', 'id')
                ->mapWithKeys(fn ($name, $id) => [(string) $id => $name])
                ->all();
        }

        return self::$roleIdToNameMap;
    }

    /**
     * Indica si un campo debe ocultarse para el estado actual de roles.
     *
     * @param  string            $field      Nombre del campo (name, email, password, etc.)
     * @param  array|string|null $rolesState IDs de los roles seleccionados en el formulario.
     */
    protected static function campoOculto(string $field, mixed $rolesState): bool
    {
        $map = self::getRoleIdToNameMap();

        foreach ((array) $rolesState as $roleId) {
            $roleName = $map[(string) $roleId] ?? null;

            if ($roleName === null) {
                continue;
            }

            if (in_array($field, self::CAMPOS_OCULTOS_POR_ROL[$roleName] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Devuelve el closure estándar de visibilidad para un campo.
     */
    protected static function visibleSiNoOculto(string $field): \Closure
    {
        return fn (Get $get): bool => ! self::campoOculto($field, $get('roles'));
    }

    /**
     * Limpia el estado de todos los campos que quedaron ocultos con el nuevo set de roles.
     */
    protected static function limpiarCamposOcultos(Set $set, mixed $rolesState): void
    {
        $todosLosCampos = collect(self::CAMPOS_OCULTOS_POR_ROL)
            ->flatten()
            ->unique()
            ->all();

        foreach ($todosLosCampos as $field) {
            if (self::campoOculto($field, $rolesState)) {
                $set($field, null);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Formulario
    // ─────────────────────────────────────────────────────────────────────────

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
                                        ->relationship('roles', 'name', fn ($query) => $query->orderBy('name'))
                                        ->searchable()
                                        ->preload()
                                        ->default([$registradoId])
                                        ->required()
                                        ->live()
                                        ->prefixIcon('heroicon-o-shield-check')
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            // Limpia cualquier campo que ahora quede oculto
                                            self::limpiarCamposOcultos($set, $state);
                                        })
                                        ->hint('Define los permisos del usuario.'),

                                    TextInput::make('email')
                                        ->label('Correo electrónico')
                                        ->prefixIcon('heroicon-o-envelope')
                                        ->email()
                                        ->unique(ignoreRecord: true)
                                        ->visible(self::visibleSiNoOculto('email'))
                                        ->required(fn (Get $get): bool =>
                                            ! self::campoOculto('email', $get('roles'))
                                        ),

                                    TextInput::make('password')
                                        ->label('Contraseña')
                                        ->password()
                                        ->revealable()
                                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                        ->dehydrated(fn ($state) => filled($state))
                                        ->visible(self::visibleSiNoOculto('password'))
                                        ->required(fn (Get $get, string $operation): bool =>
                                            $operation === 'create'
                                            && ! self::campoOculto('password', $get('roles'))
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
                                        ->visible(self::visibleSiNoOculto('password_confirmation'))
                                        ->required(fn (Get $get, string $operation): bool =>
                                            $operation === 'create'
                                            && ! self::campoOculto('password_confirmation', $get('roles'))
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
                                    ->prefixIcon('heroicon-o-user')
                                    ->visible(self::visibleSiNoOculto('last_name')),

                                TextInput::make('second_last_name')
                                    ->label('Segundo apellido')
                                    ->prefixIcon('heroicon-o-user')
                                    ->visible(self::visibleSiNoOculto('second_last_name')),

                                DatePicker::make('birth_date')
                                    ->label('Fecha de nacimiento')
                                    ->displayFormat('d/m/Y')
                                    ->prefixIcon('heroicon-o-cake')
                                    ->placeholder('dd/mm/aaaa')
                                    ->visible(self::visibleSiNoOculto('birth_date')),

                                Select::make('gender')
                                    ->label('Género')
                                    ->options([
                                        'male'   => 'Masculino',
                                        'female' => 'Femenino',
                                        'other'  => 'Otro',
                                    ])
                                    ->placeholder('Seleccionar')
                                    ->prefixIcon('heroicon-o-user-circle')
                                    ->native(false)
                                    ->visible(self::visibleSiNoOculto('gender')),

                                Select::make('marital_status')
                                    ->label('Estado civil')
                                    ->options([
                                        'child'         => 'Niño/a',
                                        'young'         => 'Joven',
                                        'single'        => 'Solo/a',
                                        'married_young' => 'Casado/a Chico',
                                        'married_adult' => 'Casado/a Mediano',
                                        'married_old'   => 'Casado/a Grande',
                                    ])
                                    ->placeholder('Seleccionar')
                                    ->prefixIcon('heroicon-o-heart')
                                    ->native(false)
                                    ->visible(self::visibleSiNoOculto('marital_status')),

                                TextInput::make('phone')
                                    ->label('Celular')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->tel()
                                    ->visible(self::visibleSiNoOculto('phone')),

                                Textarea::make('address')
                                    ->label('Dirección')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->visible(self::visibleSiNoOculto('address')),
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
                                        fn ($query) => $query->where('active', true)->orderBy('name')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('guard_day', null))
                                    ->prefixIcon('heroicon-o-rectangle-group')
                                    ->hint('Solo grupos activos.')
                                    ->required()
                                    ->columnSpanFull()
                                    ->visible(self::visibleSiNoOculto('groups')),

                                // Visible si: (a) el rol no lo oculta
                                //          y (b) algún grupo seleccionado requiere día de guardia.
                                Select::make('guard_day')
                                    ->label('Día de guardia')
                                    ->options(GuardDay::options())
                                    ->prefixIcon('heroicon-o-calendar-days')
                                    ->native(false)
                                    ->placeholder('Seleccionar día...')
                                    ->visible(function (Get $get): bool {
                                        if (self::campoOculto('guard_day', $get('roles'))) {
                                            return false;
                                        }

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
                                    ->displayFormat('d/m/Y') // Lo que ve y escribe el usuario (ej. 30/04/2026)
                                    ->format('Y-m-d')         // Formato que Filament envía a la BD (ej. 2026-04-30)
                                    ->placeholder('dd/mm/aaaa')
                                    ->closeOnDateSelection()
                                    ->visible(self::visibleSiNoOculto('join_date')),

                                Toggle::make('recommendation_letter')
                                    ->label('Entregó carta de recomendación')
                                    ->helperText('Marca si el usuario entregó su carta.')
                                    ->inline(false)
                                    ->columnSpanFull()
                                    ->visible(self::visibleSiNoOculto('recommendation_letter')),
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
                                    ->columnSpanFull()
                                    ->visible(self::visibleSiNoOculto('observations')),
                            ]),

                    ]),

            ]);
    }
}