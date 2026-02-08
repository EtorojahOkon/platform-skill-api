<?php

namespace App\Services\V1;

use App\Enums\AssessmentStatus;
use App\Jobs\EvaluateAssessmentJob;
use App\Models\Assessment;
use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Facades\Storage;

class AssessmentService
{
    public function createAssessment(string $userId, ?string $lessonId = null, ?string $taskId = null): Assessment
    {
        $assessableType = 'platform';
        $assessableId = null;

        if ($lessonId) {
            $assessableType = 'lesson';
            $assessableId = $lessonId;
        } elseif ($taskId) {
            $assessableType = 'task';
            $assessableId = $taskId;
        }

        return Assessment::create([
            'user_id' => $userId,
            'assessable_type' => $assessableType,
            'assessable_id' => $assessableId,
        ]);
    }

    public function appendVideoChunk(Assessment $assessment, $chunk): bool
    {
        $tempPath = "assessments/{$assessment->id}/temp_video.webm";

        if (!Storage::disk('local')->exists("assessments/{$assessment->id}")) {
            Storage::disk('local')->makeDirectory("assessments/{$assessment->id}");
        }

        $absolutePath = Storage::disk('local')->path($tempPath);

        $fileHandle = fopen($absolutePath, 'ab');
        if ($fileHandle === false) {
            return false;
        }

        fwrite($fileHandle, file_get_contents($chunk->getRealPath()));
        fclose($fileHandle);

        return true;
    }

    public function processVideoUpload(Assessment $assessment): void
    {
        $tempPath = "assessments/{$assessment->id}/temp_video.webm";
        $finalPath = "assessments/{$assessment->id}/video.webm";

        if (!Storage::disk('local')->exists($tempPath)) {
            throw new \Exception('Unable to open file');
        }

        $stream = fopen(Storage::disk('local')->path($tempPath), 'r');
        Storage::disk('r2')->put($finalPath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        Storage::disk('local')->delete($tempPath);
    }

    public function prepareGeminiParts(Assessment $assessment): array
    {
        $parts = [];
        $parts[] = "Analyze this video recording of a user performing a task.";

        $path = "assessments/{$assessment->id}/video.webm";
        if (Storage::disk('r2')->exists($path)) {
            $videoData = Storage::disk('r2')->get($path);
            $parts[] = new Blob(
                mimeType: MimeType::VIDEO_WEBM,
                data: base64_encode($videoData)
            );
        }

        $parts[] = match ($assessment->assessable_type) {
            'lesson' => "Analyze the user's performance on this lesson: {$assessment->assessable->title}. Provide constructive feedback and generate practice tasks based on the lesson content.",
            'task' => "Evaluate the user's completion of the task: {$assessment->assessable->description}. improved rating and proficiency If the user has completed the task successfully, mark it as completed.",
            'platform' => "Comments should be person directed not generic. Generate a course path. Always generate full course path with all the modules and lessons according to the structure given.",
            default => throw new \InvalidArgumentException('Invalid assessable type'),
        };

        return $parts;
    }

    public function getGenerationConfig(Assessment $assessment): GenerationConfig
    {
        $config = match ($assessment->assessable_type) {
            'lesson' => $this->getLessonAssessmentConfig(),
            'task' => $this->getTaskAssessmentConfig(),
            'platform' => $this->getPlatformAssessmentConfig(),
            default => throw new \InvalidArgumentException('Invalid assessable type'),
        };

        return $config;
    }

    private function getPlatformAssessmentConfig(): GenerationConfig
    {
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'platform' => new Schema(type: DataType::STRING),
                'proficiency' => new Schema(type: DataType::STRING),
                'comments' => new Schema(
                    type: DataType::ARRAY,
                    items: new Schema(type: DataType::STRING)
                ),
                'course' => new Schema(
                    type: DataType::OBJECT,
                    properties: [
                        'title' => new Schema(type: DataType::STRING),
                        'modules' => new Schema(
                            type: DataType::ARRAY,
                            items: new Schema(
                                type: DataType::OBJECT,
                                properties: [
                                    'title' => new Schema(type: DataType::STRING),
                                    'lessons' => new Schema(
                                        type: DataType::ARRAY,
                                        items: new Schema(type: DataType::STRING)
                                    ),
                                ]
                            )
                        ),
                    ]
                ),
            ],
            required: ['platform', 'proficiency', 'comments', 'course']
        );

        return new GenerationConfig(
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
            responseSchema: $schema
        );
    }

    private function getLessonAssessmentConfig(): GenerationConfig
    {
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'proficiency' => new Schema(type: DataType::STRING),
                'rating' => new Schema(type: DataType::INTEGER),
                'comments' => new Schema(
                    type: DataType::ARRAY,
                    items: new Schema(type: DataType::STRING)
                ),
                'tasks' => new Schema(
                    type: DataType::ARRAY,
                    items: new Schema(
                        type: DataType::OBJECT,
                        properties: [
                            'description' => new Schema(type: DataType::STRING),
                        ]
                    )
                ),
            ],
            required: ['proficiency', 'rating', 'comments', 'tasks']
        );

        return new GenerationConfig(
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
            responseSchema: $schema
        );
    }

    private function getTaskAssessmentConfig(): GenerationConfig
    {
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'proficiency' => new Schema(type: DataType::STRING),
                'rating' => new Schema(type: DataType::INTEGER),
                'comments' => new Schema(
                    type: DataType::ARRAY,
                    items: new Schema(type: DataType::STRING)
                ),
                'is_completed' => new Schema(type: DataType::BOOLEAN),
            ],
            required: ['proficiency', 'rating', 'comments', 'is_completed']
        );

        return new GenerationConfig(
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
            responseSchema: $schema
        );
    }

    public function dispatchEvaluation(Assessment $assessment): void
    {
        $assessment->update(['status' => AssessmentStatus::PROCESSING]);

        EvaluateAssessmentJob::dispatch($assessment);
    }
}
