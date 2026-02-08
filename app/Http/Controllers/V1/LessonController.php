<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Http\Resources\LessonResource;
use App\Services\V1\LessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __construct(
        private readonly LessonService $lessonService
    ) {}

    public function show(Lesson $lesson): JsonResponse
    {
        $lessonDetails = $this->lessonService->getLessonDetails($lesson);

        return $this->successResponse('Lesson retrieved successfully', [
            'lesson' => new LessonResource($lessonDetails),
        ]);
    }
}
