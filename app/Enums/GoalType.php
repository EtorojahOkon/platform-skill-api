<?php

namespace App\Enums;

enum GoalType: string
{
    case TASK = 'task';
    case LESSON = 'lesson';
    case COURSE = 'course';
    case PLATFORM = 'platform';
}
