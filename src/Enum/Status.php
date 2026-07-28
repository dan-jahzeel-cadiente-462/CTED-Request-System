<?php

namespace App\Enum;

enum Status: string 
{
    case COMPLETED = "Completed";
    case CANCELLED = "Cancelled";
    case PENDING = "Pending";
    
    public const ALL = [
        'Completed',
        'Cancelled',
        "Pending"
    ];

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::PENDING => 'Pending',
        };
    }
}

?>