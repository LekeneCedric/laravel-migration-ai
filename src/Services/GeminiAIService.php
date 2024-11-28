<?php

namespace CedricLekene\LaravelMigrationAI\Services;

use CedricLekene\LaravelMigrationAI\contracts\AIService;
use CedricLekene\LaravelMigrationAI\Dto\MigrationContentDto;
use CedricLekene\LaravelMigrationAI\Infrastructure\HttpClient;

class GeminiAIService implements AIService
{

    public function execute(string $apiKey, string $model, bool $isCreate, string $description): MigrationContentDto
    {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ':generateContent' . '?key=' . $apiKey;
        $prompt = [
            [
                'text' => $this->buildPrompt(description: $description, isCreate: $isCreate),
            ]
        ];

        $requestBody = [
            "contents" => [
                "parts" => $prompt
            ],
            "generationConfig" => [
                "temperature" => 0.0,
                "response_mime_type" => "application/json",
            ],
        ];

        $headers = [
            'Content-Type: application/json',
        ];
        $response = HttpClient::httpCall($apiUrl, 'POST', $headers, $requestBody);
        if (!$response) {
            return new MigrationContentDto(message: 'Something went wrong dude , try again !');
        }
        $cleanedResponse = $response['candidates'][0]['content']['parts'][0]['text'];
        $responseData = json_decode($cleanedResponse, true);
        return new MigrationContentDto(
            migrationUp: $responseData['content'],
            migrationDown: $responseData['reverse_content'] ?? ''
        );
    }

    private function buildPrompt(string $description, bool $isCreate): string
    {
        $action = $isCreate ? 'create a new table' : 'update an existing table';
        return "You are a Laravel migration assistant. Given the description and action:\n".
                "- Description: $description\n" .
                "- Action: $action\n\n".
                "Output:".
                "{\n".
                "  \"content\": \"content inside function(Blueprint \$table) { }\",\n" .
                "  \"reverse_content\": \"content inside function down() { }\"\n" .
                "}\n".
                "Examples:\n" .
                "1. Updating table with fields `name`, `type`:\n" .
                "{\n".
                "  \"content\": \"\$table->string('name', 200); \$table->enum('type', ['card', 'bank']);\"".
                "  \"reverse_content\": \"\$table->dropColumn('name'); \$table->dropColumn('type');\"\n".
                "}\n\n".
                "2. Creating table `users`:\n".
                "{\n".
                "  \"content\": \"\$table->string('name', 200); \$table->enum('type', ['card', 'bank']);\",\n".
                "}";
    }
}