<?php

namespace App\Enum;

enum Program: string
{
    case BEED = "Bachelor of Elementary Education";
    case BECED = "Bachelor of Early Childhood Education";
    case BSNED = "Bachelor of Special Needs Education";
    case BTLED = "Bachelor of Technology and Livelihood Education";
    case BPED = "Bachelor of Physical Education";
    case BSED_ENGLISH = "Bachelor of Secondary Education Major in English";
    case BSED_FILIPINO = "Bachelor of Secondary Education Major in Filipino";
    case BSED_MATHEMATICS = "Bachelor of Secondary Education Major in Mathematics";
    case BSED_SOCIAL_STUDIES = "Bachelor of Secondary Education Major in Social Studies";
    case BSED_VALED = "Bachelor of Secondary Education Major in Values Education";
    case OTHER = "Other";


    public const ALL = [
        'Bachelor of Elementary Education',
        'Bachelor of Early Childhood Education',
        'Bachelor of Special Needs Education',
        'Bachelor of Technology and Livelihood Education',
        'Bachelor of Physical Education',
        'Bachelor of Secondary Education Major in English',
        'Bachelor of Secondary Education Major in Filipino',
        'Bachelor of Secondary Education Major in Mathematics',
        'Bachelor of Secondary Education Major in Social Studies',
        'Bachelor of Secondary Education Major in Values Education',
        'Other',
    ];

    public function label(): string
    {
        return match ($this) {
            self::BEED => 'Bachelor of Elementary Education',
            self::BECED => 'Bachelor of Early Childhood Education',
            self::BSNED => 'Bachelor of Special Needs Education',
            self::BTLED => 'Bachelor of Technology and Livelihood Education',
            self::BPED => 'Bachelor of Physical Education',
            self::BSED_ENGLISH => 'Bachelor of Secondary Education Major in English',
            self::BSED_FILIPINO => 'Bachelor of Secondary Education Major in Filipino',
            self::BSED_MATHEMATICS => 'Bachelor of Secondary Education Major in Mathematics',
            self::BSED_SOCIAL_STUDIES => 'Bachelor of Secondary Education Major in Social Studies',
            self::BSED_VALED => 'Bachelor of Secondary Education Major in Values Education',
            self::OTHER => 'Other',
        };
    }
}
?>