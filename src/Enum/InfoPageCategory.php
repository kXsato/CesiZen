<?php

namespace App\Enum;

enum InfoPageCategory: string
{
    case LEGAL     = 'legal';
    case PAGE      = 'page';
    case PAGE_ROOT = 'root';

    public function label(): string
    {
        return match($this) {
            self::LEGAL     => 'Page légale',
            self::PAGE      => 'Page d\'information',
            self::PAGE_ROOT => 'Page racine (/{slug})',
        };
    }
}
