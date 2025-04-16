<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case OPEN = 'open';
    case PARTIAL = 'partial';
    case FILLED = 'filled';
}
