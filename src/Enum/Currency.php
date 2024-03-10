<?php declare(strict_types=1);

namespace App\Enum;

enum Currency: string
{
    case USD = 'USD';
    case CAD = 'CAD';
    case EURO = 'EUR';
}