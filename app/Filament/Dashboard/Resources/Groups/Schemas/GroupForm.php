<?php

namespace App\Filament\Dashboard\Resources\Groups\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),

                TextInput::make('description')
                    ->label('Descripción'),

                Toggle::make('active')
                    ->label('Activo')
                    ->required(),

                Toggle::make('requires_guard_day')
                    ->label('Requiere día de guardia')
                    ->required(),

                // ── Administrador principal ───────────────────────────────

                Select::make('admin_role_filter')
                    ->label('Rol del administrador')
                    ->placeholder('Seleccionar rol...')
                    ->options(fn() => Role::orderBy('name')->pluck('name', 'name'))
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('admin_user_id', null))
                    ->dehydrated(false),

                Select::make('admin_user_id')
                    ->label('Administrador principal')
                    ->placeholder(fn(Get $get) => $get('admin_role_filter')
                        ? 'Seleccionar usuario...'
                        : 'Primero selecciona un rol')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->disabled(fn(Get $get) => blank($get('admin_role_filter')))
                    ->options(function (Get $get): array {
                        $role = $get('admin_role_filter');

                        if (blank($role)) {
                            return [];
                        }

                        $auxiliaries = $get('auxiliaries') ?? [];

                        return User::query()
                            ->whereHas('roles', fn($q) => $q->where('name', $role))
                            ->when(
                                ! empty($auxiliaries),
                                fn($q) => $q->whereNotIn('id', $auxiliaries)
                            )
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->hint(function (Get $get): ?string {
                        $role = $get('admin_role_filter');

                        if (blank($role)) {
                            return null;
                        }

                        $exists = User::whereHas(
                            'roles',
                            fn($q) => $q->where('name', $role)
                        )->exists();

                        return $exists
                            ? null
                            : "No hay usuarios con el rol \"{$role}\".";
                    })
                    ->hintColor('warning')
                    ->live(),

                // ── Auxiliares (múltiples) ────────────────────────────────

                Select::make('aux_role_filter')
                    ->label('Rol de los auxiliares')
                    ->placeholder('Seleccionar rol...')
                    ->options(fn() => Role::orderBy('name')->pluck('name', 'name'))
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('auxiliaries', []))
                    ->dehydrated(false),

                Select::make('auxiliaries')
                    ->label('Auxiliares')
                    ->placeholder(fn(Get $get) => $get('aux_role_filter')
                        ? 'Seleccionar auxiliares...'
                        : 'Primero selecciona un rol')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->disabled(fn(Get $get) => blank($get('aux_role_filter')))
                    ->relationship('auxiliaries', 'name')
                    ->options(function (Get $get): array {
                        $role = $get('aux_role_filter');

                        if (blank($role)) {
                            return [];
                        }

                        $adminId = $get('admin_user_id');

                        return User::query()
                            ->whereHas('roles', fn($q) => $q->where('name', $role))
                            ->when(
                                ! empty($adminId),
                                fn($q) => $q->where('id', '!=', $adminId)
                            )
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->hint(function (Get $get): ?string {
                        $role = $get('aux_role_filter');

                        if (blank($role)) {
                            return null;
                        }

                        $exists = User::whereHas(
                            'roles',
                            fn($q) => $q->where('name', $role)
                        )->exists();

                        return $exists
                            ? null
                            : "No hay usuarios con el rol \"{$role}\".";
                    })
                    ->hintColor('warning')
                    ->live(),

            ]);
    }
}
