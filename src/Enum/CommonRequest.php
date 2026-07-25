<?php

namespace App\Enum;

enum CommonRequest: string
{
    case CLEARANCE = "Clearance";
    case EVALUATION = "Evaluation";
    case PROSPECTUS = "Prospectus";
    case CERTIFIED_TRUE_COPY = "Certified True Copy";
    case REQUEST_TO_USE_CTED_FACILITIES = "Request to Use CTED Rooms/Halls";
    case REQUEST_TO_USE_DEVICES = "Request to Use Devices (Microphone, Speakers, Projector, HDMI, etc.)";
    case CERTIFICATE_OF_EMPLOYMENT_LOCAL = "Certificate of Enrollment (Local)";
    case CERTIFICATE_OF_EMPLOYMENT_ABROAD = "Certificate of Enrollment (Abroad)";

    public const ALL = [
        'Clearance',
        'Evaluation',
        'Prospectus',
        'Certified True Copy',
        'Request to Use CTED Rooms/Halls',
        'Request to Use Devices (Microphone, Speakers, Projector, HDMI, etc.)',
        'Certificate of Enrollment (Local)',
        'Certificate of Enrollment (Abroad)'
    ];

    public function label(): string
    {
        return match ($this) {
            self::CLEARANCE => 'Clearance',
            self::EVALUATION => 'Evaluation',
            self::PROSPECTUS => 'Prospectus',
            self::CERTIFIED_TRUE_COPY => 'Certified True Copy',
            self::REQUEST_TO_USE_CTED_FACILITIES => 'Request to Use CTED Rooms/Halls',
            self::REQUEST_TO_USE_DEVICES => 'Request to Use Devices (Microphone, Speakers, Projector, HDMI, etc.)',
            self::CERTIFICATE_OF_EMPLOYMENT_LOCAL => 'Certificate of Enrollment (Local)',
            self::CERTIFICATE_OF_EMPLOYMENT_ABROAD => 'Certificate of Enrollment (Abroad)',
        };
    }
}

?>