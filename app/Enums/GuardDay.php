<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\Builder;

enum GuardDay: string
{
    case Monday    = 'monday';
    case Tuesday   = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday  = 'thursday';
    case Friday    = 'friday';
    case Saturday  = 'saturday';
    case Sunday    = 'sunday';
    case Baja      = 'baja';

    public function label(): string
    {
        return match ($this) {
            self::Monday    => 'Lunes',
            self::Tuesday   => 'Martes',
            self::Wednesday => 'Miércoles',
            self::Thursday  => 'Jueves',
            self::Friday    => 'Viernes',
            self::Saturday  => 'Sábado',
            self::Sunday    => 'Domingo',
            self::Baja      => 'Baja',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $day) => [$day->value => $day->label()])
            ->toArray();
    }

    /**
     * Aplica el filtro de búsqueda al query comparando contra la etiqueta traducida (español)
     * y el valor almacenado en la BD (inglés).
     */
    public static function search(Builder $query, string $search, string $column = 'guard_day'): Builder
    {
        $searchLower = mb_strtolower(trim($search));

        $matchingValues = collect(self::cases())
            ->filter(fn (self $day) => str_contains(mb_strtolower($day->label()), $searchLower))
            ->pluck('value')
            ->toArray();

        return $query->where(function (Builder $subQuery) use ($column, $search, $matchingValues) {
            $subQuery->where($column, 'like', "%{$search}%");

            if (!empty($matchingValues)) {
                $subQuery->orWhereIn($column, $matchingValues);
            }
        });
    }
}
