<?php

namespace App\Enum;

enum AssetStatus: string 
{
    case AVAILABLE = "Available";
    case RESERVED = "Reserved";
    case MAINTENANCE = "Maintenance";
    case DECOMMISSIONED = "Decommissioned";
    
    public const ALL = [
        'Available',
        'Reserved',
        'Maintenance',
        'Decommissioned'
    ];

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::RESERVED => 'Reserved',
            self::MAINTENANCE => 'Maintenance',
            self::DECOMMISSIONED => 'Decommissioned',
        };
    }
}

?>