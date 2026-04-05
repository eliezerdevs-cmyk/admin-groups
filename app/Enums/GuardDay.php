<?php

namespace App\Enums;

enum GuardDay: string
{
    case Monday    = 'monday';
    case Tuesday   = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday  = 'thursday';
    case Friday    = 'friday';
    case Saturday  = 'saturday';
    case Sunday    = 'sunday';

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
        };
    }

    /**
     * Retorna el array listo para opciones de Filament Select.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $day) => [$day->value => $day->label()])
            ->toArray();
    }
}
