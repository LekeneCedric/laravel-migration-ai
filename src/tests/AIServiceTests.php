<?php

namespace CedricLekene\LaravelMigrationAI\tests;

use CedricLekene\LaravelMigrationAI\contracts\AIService;
use CedricLekene\LaravelMigrationAI\Enums\EnvironmentVariablesEnum;
use CedricLekene\LaravelMigrationAI\Http\HttpClient;
use CedricLekene\LaravelMigrationAI\Http\Services\GeminiAIService;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class AIServiceTests extends TestCase
{
    private AIService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        m::getConfiguration()->allowMockingNonExistentMethods();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function test_gemini_ai_service_return_create_migration_contents()
    {
        $httpClientMock = m::mock('alias:'.HttpClient::class);
        $httpClientMock
            ->shouldReceive('httpCall')
            ->once()
            ->andReturn([
                'status' => 200,
                'response' => [
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'content' => 'content inside function(Blueprint $table) { }',
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ]]);

        $this->aiService = new GeminiAIService();
        $response = $this->aiService->execute(
            apiKey: 'FAKE_API_KEY',
            model: EnvironmentVariablesEnum::DEFAULT_GEMINI_MODEL->value,
            isCreate: true,
            description: 'create users table with field name, and category_card (enum[card|bank])'
        );

        $this->assertEquals('content inside function(Blueprint $table) { }', $response->migrationUp);
        $this->assertEmpty($response->migrationDown);
    }

    public function test_gemini_ai_service_return_update_migration_contents()
    {
        m::getConfiguration()->allowMockingNonExistentMethods();
        $httpClientMock = m::mock('alias:'.HttpClient::class);

        $httpClientMock
            ->shouldReceive('httpCall')
            ->once()
            ->andReturn([
                'status' => 200,
                'response' => [
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [
                                        'text' => json_encode([
                                            'content' => 'content inside function(Blueprint $table) { }',
                                            'reverse_content' => 'content inside function down() { }'
                                        ])
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]]);

        $this->aiService = new GeminiAIService();
        $response = $this->aiService->execute(
            apiKey: 'FAKE_API_KEY',
            model: EnvironmentVariablesEnum::DEFAULT_GEMINI_MODEL->value,
            isCreate: false,
            description: 'update field name to title, and type to category(enum[card|bank])'
        );

        $this->assertEquals('content inside function(Blueprint $table) { }', $response->migrationUp);
        $this->assertEquals('content inside function down() { }', $response->migrationDown);
    }
    public function test_gemini_ai_service_return_error_message()
    {
        m::getConfiguration()->allowMockingNonExistentMethods();
        $httpClientMock = m::mock('alias:'.HttpClient::class);

        $httpClientMock
            ->shouldReceive('httpCall')
            ->once()
            ->andReturn([
                'status' => 200,
                'response' => [
                    'error' => [
                        [
                            'message' => '"The input provided is not in the correct format.',
                        ]
                    ]
                ]]);

        $this->aiService = new GeminiAIService();
        $response = $this->aiService->execute(
            apiKey: 'FAKE_API_KEY',
            model: EnvironmentVariablesEnum::DEFAULT_GEMINI_MODEL->value,
            isCreate: false,
            description: 'update field name to title, and type to category(enum[card|bank])'
        );

        $this->assertNotEmpty($response->message);
        $this->assertEmpty($response->migrationUp);
        $this->assertEmpty($response->migrationDown);
    }
}