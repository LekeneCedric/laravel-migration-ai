<?php

namespace CedricLekene\LaravelMigrationAI\Http\Services;

use CedricLekene\LaravelMigrationAI\contracts\AIService;
use CedricLekene\LaravelMigrationAI\Dto\MigrationContentDto;
use CedricLekene\LaravelMigrationAI\Enums\ErrorMessagesEnum;
use CedricLekene\LaravelMigrationAI\Http\HttpClient;
use Exception;

class GeminiAIService implements AIService
{
    public function execute(string $apiKey, string $model, bool $isCreate, string $tableName, string $description): MigrationContentDto
    {
        $apiUrl = (env('GEMINI_API_URL') ?? "https://generativelanguage.googleapis.com/v1beta/models/") . $model . ':generateContent' . '?key=' . $apiKey;
        $prompt = [
            ['text' => 'table name : ' . $tableName],
            ['text' => $this->buildPrompt(description: $description, isCreate: $isCreate)]
        ];
        $headers = [
            'Content-Type: Application/json',
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

        $httpResponse = httpClient::httpCall($apiUrl, 'POST', $headers, $requestBody);

        try {
            if (!$httpResponse['status']) {
                return new MigrationContentDto(message: $httpResponse['response']['error']['message']);
            }
            $cleanedResponse = $httpResponse['response']['candidates'][0]['content']['parts'][0]['text'];
            $responseData = json_decode($cleanedResponse, true);
            return new MigrationContentDto(
                migrationUp: $responseData['content'],
                migrationDown: $responseData['reverse_content'] ?? ''
            );
        } catch (Exception) {
            return new MigrationContentDto(
                message: ErrorMessagesEnum::SOMETHING_WENT_WRONG->value
            );
        }
    }

    private function buildPrompt(string $description, bool $isCreate): string
    {
        $action = $isCreate ? 'create a new table' : 'update an existing table';
        return "You are a Laravel migration assistant. Given the description and action:\n" .
            "- Description: $description\n" .
            "- Action: $action\n" .
            "Output:" .
            "{\n" .
            "  \"content\": \"content inside function(Blueprint \$table) { }\",\n" .
            "  \"reverse_content\": \"content inside function down() { }\"\n" .
            "}\n" .
            "Examples:\n" .
            "1. Updating table with fields `name`, `type`:\n" .
            "{\n" .
            "  \"content\": \"\$table->string('name', 200); \$table->enum('type', ['card', 'bank']);\"" .
            "  \"reverse_content\": \"\$table->dropColumn('name'); \$table->dropColumn('type');\"\n" .
            "}\n" .
            "2. Creating table `users`:\n" .
            "{\n" .
            "  \"content\": \"\$table->string('name', 200); \$table->enum('type', ['card', 'bank']);\",\n" .
            "}";
    }
}