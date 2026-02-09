<?php

namespace App\Http\Controllers\V1;

use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssessmentResource;
use App\Models\Assessment;
use App\Services\V1\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class AssessmentController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessmentService
    ) {}

    public function startAssessment(Request $request): JsonResponse
    {
        $request->validate([
            'lesson_id' => 'nullable|exists:lessons,id',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $assessment = $this->assessmentService->createAssessment(
            $request->user()->id,
            $request->lesson_id,
            $request->task_id
        );

        return $this->successResponse('Assessment started successfully', [
            'assessment_id' => $assessment->id,
        ]);
    }

    public function uploadChunk(Request $request, Assessment $assessment): JsonResponse
    {
        $request->validate([
            'video_chunk' => 'required|file',
        ]);

        $chunk = $request->file('video_chunk');

        if (!$this->assessmentService->appendVideoChunk($assessment, $chunk)) {
            return $this->badRequestResponse('Unable to open file');
        }

        return $this->successResponse('Chunk appended successfully');
    }

    public function evaluateAssessment(Request $request, Assessment $assessment): JsonResponse
    {
        if ($assessment->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if ($assessment->status !== AssessmentStatus::PENDING) {
            return $this->badRequestResponse("Assessment is not in progress");
        }

        $this->assessmentService->processVideoUpload($assessment);

        $this->assessmentService->dispatchEvaluation($assessment);

        return $this->successResponse('Assessment evaluation started', [
            'assessment' => new AssessmentResource($assessment->refresh()),
        ]);
    }

    public function getAssessment(Request $request, Assessment $assessment): JsonResponse
    {
        if ($assessment->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $relationship = match ($assessment->assessable_type) {
            'platform' => "assessable.course.modules.lessons",
            'lesson' => "assessable.tasks",
            default => "assessable",
        };

        $assessment->load($relationship);

        return $this->successResponse('Assessment retrieved successfully', [
            'assessment' => new AssessmentResource($assessment),
        ]);
    }
}
