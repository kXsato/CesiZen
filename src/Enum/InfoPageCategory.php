<?php

namespace App\Enum;

enum InfoPageCategory: string
{
    case LEGAL = 'legal';
    case PAGE = 'page';

    public function label(): string
    {
        return match($this) {
            self::LEGAL => 'Page légale',
            self::PAGE  => 'Page d\'information',
        };
    }
}
