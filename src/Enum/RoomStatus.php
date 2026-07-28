<?php

namespace App\Enum;

enum RoomStatus: string 
{
    case AVAILABLE = "Available";
    case RESERVED = "Reserved/In use";
    case MAINTENANCE = "Maintenance";
    
    public const ALL = [
        'Available',
        'Reserved/In use',
        'Maintenance',
    ];

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::RESERVED => 'Reserved/In use',
            self::MAINTENANCE => 'Maintenance',
        };
    }
}

?>