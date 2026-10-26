<?php

namespace App\Filament\Dashboard\Resources\Activities\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Schemas\Components\Section;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la actividad')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre de la actividad')
                            ->required(),
                        TextArea::make('description')
                            ->label('Descripción de la actividad')
                            ->required(),
                        Toggle::make('active')
                            ->label('Activo')
                            ->required()
                            ->default(true),
                        Toggle::make('requires_guard_day')
                            ->label('Requiere día de guardia')
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
