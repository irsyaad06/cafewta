<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TableStatus: string implements HasLabel
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case Inactive = 'inactive';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Occupied => 'Terisi',
            self::Reserved => 'Dipesan',
            self::Inactive => 'Non-aktif',
        };
    }
}
