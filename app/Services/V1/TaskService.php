<?php

namespace App\Services\V1;

use App\Models\Task;

class TaskService
{
    public function getTaskDetails(Task $task): Task
    {
        return $task->load(['lesson.module.course', 'assessments']);
    }
}
