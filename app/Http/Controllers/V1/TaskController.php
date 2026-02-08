<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Services\V1\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function show(Task $task): JsonResponse
    {
        $taskDetails = $this->taskService->getTaskDetails($task);

        return $this->successResponse('Task retrieved successfully', [
            'task' => new TaskResource($taskDetails),
        ]);
    }
}
