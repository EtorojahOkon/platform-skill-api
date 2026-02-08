<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'total_lessons' => $this->total_lessons,
            'completed_lessons' => $this->completed_lessons,
            'progress_percentage' => $this->progress_percentage,
        ];
    }
}

