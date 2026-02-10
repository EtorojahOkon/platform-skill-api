<?php

namespace App\Services\V1;

use App\Contracts\GeminiInterface;
use Gemini;
use Gemini\Data\GenerationConfig;

class GeminiService implements GeminiInterface
{
    public function generateContent(array $parts, GenerationConfig $config): string
    {
        $gemini = Gemini::client(config('gemini.api_key'));

        $result = $gemini->generativeModel('models/gemini-3-pro-preview')
            ->withGenerationConfig($config)
            ->generateContent($parts);

        return $result->text();
    }
}
