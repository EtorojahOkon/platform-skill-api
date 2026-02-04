<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $seed = $this->trimName($this->name);
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => "https://api.dicebear.com/9.x/avataaars/png?seed={$seed}",
            'email' => $this->email,
            'is_email_verified' => $this->email_verified_at !== null,
            'created_at' => $this->created_at,
        ];
    }

    private function trimName(string $name): string
    {
        return Str::of($name)->camel()->toString();
    }
}
