<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = Course::where('user_id', $request->user()->id)
            ->with(['modules.lessons.tasks'])
            ->latest()
            ->get();

        return $this->successResponse('Courses retrieved successfully', [
            'courses' => CourseResource::collection($courses),
        ]);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        if ($course->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $course->load(['modules.lessons.tasks']);

        return $this->successResponse('Course retrieved successfully', [
            'course' => new CourseResource($course),
        ]);
    }
}

