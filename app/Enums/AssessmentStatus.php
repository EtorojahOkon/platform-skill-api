<?php

namespace App\Enums;

enum AssessmentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
}
