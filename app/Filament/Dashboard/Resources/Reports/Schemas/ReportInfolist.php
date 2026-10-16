<?php

namespace App\Filament\Dashboard\Resources\Reports\Schemas;

use App\Enums\GuardDay;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Carbon\Carbon;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\Components;


class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextEntry::make('group.name')
                                    ->label('Grupo')
                                    ->extraAttributes([
                                            'style' => 'font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.5rem; display: block;'
                                        ]),
                                TextEntry::make('day')
                                    ->label('Día de guardia')
                                    ->formatStateUsing(fn(string $state): string => GuardDay::from($state)->label())
                                    ->placeholder('-'),
                                TextEntry::make('user.name')
                                    ->label('Usuario del registro'),
                                TextEntry::make('date')
                                    ->label('Fecha del reporte')
                                    ->date()
                                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->format('d-M-Y')),
                                TextEntry::make('street_start')
                                    ->label('Calle inicial'),
                                TextEntry::make('streets_traveled')
                                    ->label('Calles recorridas'),
                            ]),
                            Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextEntry::make('street_end')
                                    ->label('Calle final'),
                                TextEntry::make('houses_visited')
                                    ->label('Casas visitadas')
                                    ->numeric(),
                                TextEntry::make('houses_opened')
                                    ->label('Casas abiertas')
                                    ->numeric(),
                                TextEntry::make('houses_didnotopen')
                                    ->label('Casas no abiertas')
                                    ->numeric(),
                                TextEntry::make('candidates_baptism')
                                    ->label('Candidatos al bautismo')
                                    ->numeric(),
                                TextEntry::make('created_at')
                                    ->label('Fecha de creación')
                                    ->date()
                                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->format('d-M-Y')),
                            ]),
                    ])->columnSpanFull(),

                Section::make('')
                    ->components([
                        RepeatableEntry::make('interestedPeople')
                            ->label('Aspirantes al bautismo')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nombre'),
                                TextEntry::make('last_name')
                                    ->label('Apellido'),
                                TextEntry::make('address')
                                    ->label('Dirección'),
                                TextEntry::make('colony')
                                    ->label('Colonia'),
                                TextEntry::make('theme')
                                    ->label('Tema de la plática'),
                                TextEntry::make('notes')
                                    ->label('Notas'),
                                TextEntry::make('status')
                                    ->label('Recibimiento del hogar'),
                                TextEntry::make('is_candidate')
                                    ->label('¿Es candidato?')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No'),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }
}
