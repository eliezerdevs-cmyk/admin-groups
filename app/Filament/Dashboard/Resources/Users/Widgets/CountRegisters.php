<?php

namespace App\Filament\Dashboard\Resources\Users\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CountRegisters extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total de usuarios', \App\Models\User::count()),
            Stat::make('Total de grupos', \App\Models\Group::count()),
        ];
    }
}
