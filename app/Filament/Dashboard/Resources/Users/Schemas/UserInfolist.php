<?php

namespace App\Filament\Dashboard\Resources\Users\Schemas;

use App\Enums\GuardDay;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Cabecera: Foto y resúmen ──────────────────────────────────────
                Section::make()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 12])
                            ->schema([
                                Group::make([
                                    ImageEntry::make('photo_url')
                                        ->hiddenLabel()
                                        ->state(fn ($record) => $record->getFilamentAvatarUrl())
                                        // Esquinas redondeadas vía CSS inline para asegurar su aplicación (útil si Tailwind JIT omite la clase)
                                        ->extraImgAttributes([
                                            'style' => 'border-radius: 16px; width: 200px; height: 200px; object-fit: cover; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid white;'
                                        ])
                                ])->columnSpan(['default' => 1, 'sm' => 6, 'md' => 2]),
                                
                                Group::make([
                                    TextEntry::make('full_name')
                                        ->hiddenLabel()
                                        ->state(fn ($record) => trim(
                                            ($record->name ?? '') . ' ' . 
                                            ($record->last_name ?? '') . ' ' . 
                                            ($record->second_last_name ?? '')
                                        ))
                                        ->extraAttributes([
                                            'style' => 'font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.5rem; display: block; text-align:right;'
                                        ]),

                                    TextEntry::make('contact_summary')
                                        ->hiddenLabel()
                                        ->state(function ($record) {
                                            $parts = array_filter([$record->email, $record->phone]);
                                            return implode('  •  ', $parts) ?: 'Datos de contacto no disponibles';
                                        })
                                        ->color('gray')
                                        ->extraAttributes([
                                            'style' => 'font-size: 0.95rem; font-weight: 500; text-align:right;'
                                        ]),
                                ])->columnSpan(['default' => 1, 'sm' => 6, 'md' => 10])
                                  ->extraAttributes([
                                      'style' => 'display: flex; flex-direction: column; justify-content: center;'
                                  ])
                            ])->extraAttributes(['style' => 'align-items: center;']) // Asegura que el contenido del grid se centre verticalmente
                    ]),

                // ── Personal Information ──────────────────────────────────────────
                Section::make('Información Personal')
                    ->description('Datos básicos del usuario.')
                    ->icon('heroicon-o-user')
                    ->columns(['sm' => 2, 'md' => 3])
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('last_name')
                            ->label('Apellido paterno')
                            ->placeholder('-'),

                        TextEntry::make('second_last_name')
                            ->label('Apellido materno')
                            ->placeholder('-'),

                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->icon('heroicon-m-envelope'),

                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('-')
                            ->icon('heroicon-m-phone'),

                        TextEntry::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->icon('heroicon-m-cake'),
                    ]),

                // ── Detalle Adicional ─────────────────────────────────────────────
                Section::make('Detalles Adicionales')
                    ->description('Información complementaria legal y de grupo.')
                    ->icon('heroicon-o-document-text')
                    ->columns(['sm' => 2, 'md' => 3])
                    ->schema([
                        TextEntry::make('gender')
                            ->label('Género')
                            ->badge()
                            ->placeholder('-')
                            ->formatStateUsing(fn($state): string => match ($state) {
                                'male'   => 'Masculino',
                                'female' => 'Femenino',
                                'other'  => 'Otro',
                                default  => $state ?? '-',
                            })
                            ->color(fn($state): string => match ($state) {
                                'male'   => 'info',
                                'female' => 'pink',
                                default  => 'gray',
                            }),

                        TextEntry::make('marital_status')
                            ->label('Estado civil')
                            ->badge()
                            ->placeholder('-')
                            ->formatStateUsing(fn($state): string => match ($state) {
                                'single'   => 'Soltero/a',
                                'married'  => 'Casado/a',
                                'divorced' => 'Divorciado/a',
                                'widowed'  => 'Viudo/a',
                                'other'    => 'Otro',
                                default    => $state ?? '-',
                            })
                            ->color('gray'),

                        TextEntry::make('join_date')
                            ->label('Fecha de ingreso')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->icon('heroicon-m-calendar-days'),

                        TextEntry::make('guard_day')
                            ->label('Día de guardia')
                            ->placeholder('-')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(
                                fn($state): string => $state instanceof GuardDay
                                    ? $state->label()
                                    : (GuardDay::tryFrom((string) $state)?->label() ?? $state ?? '-')
                            ),

                        IconEntry::make('recommendation_letter')
                            ->label('Carta de recomendación')
                            ->boolean(),
                    ]),

                // ── Ubicación y Notas ─────────────────────────────────────────────
                Section::make('Ubicación y Notas')
                    ->description('Dirección del usuario y observaciones registradas.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('address')
                            ->label('Dirección completa')
                            ->placeholder('-'),

                        TextEntry::make('observations')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones'),
                    ]),
            ]);
    }
}
