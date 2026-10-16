<?php

namespace App\Filament\Dashboard\Resources\Reports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use App\Models\Group;
use App\Enums\GuardDay;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('group_id')
                    ->label('Grupo')
                    ->relationship('group', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $requiresDay = Group::find($get('group_id'))?->requires_guard_day;
                        if (! $requiresDay) {
                            $set('day', null);
                        }
                    }),

                Select::make('day')
                    ->label('Día de guardia')
                    ->options(GuardDay::options())
                    ->visible(
                        fn (Get $get): bool =>
                        (bool) Group::find($get('group_id'))?->requires_guard_day
                    )
                    ->required(
                        fn (Get $get): bool =>
                        (bool) Group::find($get('group_id'))?->requires_guard_day
                    ),

                DatePicker::make('date')
                    ->label('Fecha')
                    ->default(now())
                    ->required(),

                TextInput::make('street_start')
                    ->label('Calle inicial')
                    ->required(),

                Textarea::make('streets_traveled')
                    ->label('Calles recorridas')
                    ->placeholder('Nombre de calle 1, calle 2, calle 3, ...')
                    ->rows(2)
                    ->required(),

                TextInput::make('street_end')
                    ->label('Calle final')
                    ->required(),

                TextInput::make('houses_visited')
                    ->label('Hogares visitadas')
                    ->numeric()
                    ->required(),

                TextInput::make('houses_opened')
                    ->label('Hogares abiertas')
                    ->numeric()
                    ->required(),

                TextInput::make('houses_didnotopen')
                    ->label('Hogares no abiertas')
                    ->numeric()
                    ->required(),

                TextInput::make('candidates_baptism')
                    ->label('Candidatos a bautismo')
                    ->numeric()
                    ->required(),

                // 👇 Repeater a ancho completo
                Section::make('Oyentes')
                    ->schema([
                        Repeater::make('interestedPeople')
                            ->relationship()
                            ->label('Oyentes')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label('Apellido')
                                    ->maxLength(255),
                                TextInput::make('address')
                                    ->label('Dirección')
                                    ->columnSpanFull(),
                                TextInput::make('colony')
                                    ->label('Colonia')
                                    ->required(),
                                TextInput::make('theme')
                                    ->label('Tema de la plática')
                                    ->required(),
                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->columnSpanFull()
                                    ->rows(2),
                                Select::make('status')
                                    ->label('Recibimiento del hogar')
                                    ->options([
                                        'excelente' => 'Excelente', 'bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo',
                                    ])
                                    ->default('regular'),
                                Toggle::make('is_candidate')
                                    ->label('Candidato al bautismo')
                                    ->default(false),
                            ])
                            ->columns(2) // dentro del repeater: 2 columnas para que se vea ordenado
                            ->defaultItems(0)
                            ->addActionLabel('➕ Agregar oyente')
                            ->reorderable(false)
                            ->collapsible()
                            ->cloneable(),
                    ])
                    ->columnSpanFull(), // 👈 ocupa las 3 columnas
            ])
            ->columns(4); // 👈 grilla de 3 columnas para todos los campos
    }
}