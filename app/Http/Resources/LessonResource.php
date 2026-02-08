<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'is_locked' => $this->is_locked,
            'is_completed' => $this->is_completed,
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}

