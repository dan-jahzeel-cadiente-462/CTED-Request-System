<?php

namespace App\Enum;

enum EmploymentCertificate: string 
{
    case LOCAL = 'Local';
    case INTERNATIONAL = 'International';

    public const ALL = [
        'Local',
        'International'
    ];

    public function label(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::INTERNATIONAL => 'International',
        };
    }
}

?>