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
        
        $result = $gemini->generativeModel('models/gemini-2.5-flash')
            ->withGenerationConfig($config)
            ->generateContent($parts);

        return $result->text();
    }
}

