<?php

namespace CedricLekene\LaravelMigrationAI\tests\Integration;

use CedricLekene\LaravelMigrationAI\contracts\AIService;
use CedricLekene\LaravelMigrationAI\Enums\EnvironmentVariablesEnum;
use CedricLekene\LaravelMigrationAI\Services\GeminiAIService;
use CedricLekene\LaravelMigrationAI\Services\OpenAIService;
use PHPUnit\Framework\TestCase;

class AIServiceTests extends TestCase
{
    private AIService $aiService;

    public function test_can_generate_migration_from_gemini()
    {
        $this->aiService = new GeminiAIService();
        $response = $this->aiService->execute(
            apiKey: getenv(EnvironmentVariablesEnum::GEMINI_API_KEY->value),
            model: EnvironmentVariablesEnum::DEFAULT_GEMINI_MODEL->value,
            isCreate: false,
            description: 'update field name to title, and type to category(enum[card|bank])'
        );
        $this->assertNotEmpty($response->migrationUp);
        $this->assertNotEmpty($response->migrationDown);
    }

    public function test_can_generate_migration_from_openai()
    {
        $this->aiService = new OpenAIService();
        $response = $this->aiService->execute(
            apiKey: getenv(EnvironmentVariablesEnum::OPENAI_API_KEY->value),
            model: EnvironmentVariablesEnum::DEFAULT_OPENAI_MODEL->value,
            isCreate: false,
            description: 'update field name to title, and type to category (enum[card|bank])'
        );
        $this->assertNotEmpty($response->migrationUp);
        $this->assertNotEmpty($response->migrationDown);
    }
}