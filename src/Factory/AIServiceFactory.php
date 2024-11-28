<?php

namespace CedricLekene\LaravelMigrationAI\Factory;

use CedricLekene\LaravelMigrationAI\contracts\AIService;
use CedricLekene\LaravelMigrationAI\Enums\ServiceTypeEnum;
use CedricLekene\LaravelMigrationAI\Services\GeminiAIService;
use CedricLekene\LaravelMigrationAI\Services\OpenAIService;

class AIServiceFactory
{
    public static function make(ServiceTypeEnum $service): AIService
    {
        return match ($service) {
            ServiceTypeEnum::GEMINI => new GeminiAIService(),
            ServiceTypeEnum::OPENAI => new OpenAIService()
        };

    }
}