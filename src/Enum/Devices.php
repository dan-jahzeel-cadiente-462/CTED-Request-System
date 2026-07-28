<?php

namespace App\Enum;

enum Devices: string {
    case MICROPHONE = "Microphone";
    case SPEAKERS = "Speakers";
    case PROJECTOR = "Projector";
    case HDMI = "HDMI";
    case OTHER = "Others, please specify";

    public const ALL = [
        'Microphone',
        'Speakers',
        'Projector',
        'HDMI',
        'Others, please specify'
    ];

    public function label(): string
    {
        return match($this) {
            self::MICROPHONE => 'Microphone',
            self::SPEAKERS => 'Speakers',
            self::PROJECTOR => 'Projector',
            self::HDMI => 'HDMI',
            self::OTHER => 'Others, please specify',
        };
    }
}

?>