<?php

namespace App\Services\V1;

use App\Models\Lesson;

class LessonService
{
    public function getLessonDetails(Lesson $lesson): Lesson
    {
        return $lesson->load(['module.course', 'tasks', 'assessments']);
    }
}
