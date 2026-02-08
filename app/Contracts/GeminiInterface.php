<?php

namespace App\Contracts;

use Gemini\Data\GenerationConfig;

interface GeminiInterface
{
    public function generateContent(array $parts, GenerationConfig $config): string;
}

