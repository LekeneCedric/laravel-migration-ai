<?php

namespace CedricLekene\LaravelMigrationAI\Http\Services;

use CedricLekene\LaravelMigrationAI\contracts\AIService;
use CedricLekene\LaravelMigrationAI\Dto\MigrationContentDto;
use CedricLekene\LaravelMigrationAI\Enums\ErrorMessagesEnum;
use CedricLekene\LaravelMigrationAI\Http\HttpClient;
use Exception;

class OpenAIService implements AIService
{
    public function execute(string $apiKey, string $model, bool $isCreate, string $tableName, string $description): MigrationContentDto
    {
        $apiUrl = env('OPENAI_API_URL') ?? 'https://api.openai.com/v1/chat/completions';
        $prompt = $this->buildPrompt($description, $isCreate);

        $requestBody = [
            "model" => $model,
            "messages" => $prompt,
            "temperature" => 0.0,
        ];

        $headers = [
            'Content-Type: Application/json',
            'Authorization: Bearer ' . $apiKey,
        ];

        $httpResponse = HttpClient::httpCall($apiUrl, 'POST', $headers, $requestBody);

        try {
            if (!$httpResponse['status']) {
                return new MigrationContentDto(message: $httpResponse['error']['message']);
            }
            $cleanedResponse = $httpResponse['choices'][0]['message']['content'];
            $responseData = json_decode($cleanedResponse, true);
            return new MigrationContentDto(
                migrationUp: $responseData['content'],
                migrationDown: $responseData['reverse_content'] ?? ''
            );
        } catch (Exception) {
            return new MigrationContentDto(
                message: ErrorMessagesEnum::SOMETHING_WENT_WRONG->value,
            );
        }
    }

    private function buildPrompt(string $description, bool $isCreate): array
    {
        $action = $isCreate ? 'create a new table' : 'update an existing table';
        return [
            [
                'role' => 'system',
                'content' => "You are a Laravel migration assistant. Given the description and action:\n".

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
                    "}"
            ],
            [
                'role' => 'user',
                'content' => "- Description: $description\n" .
                    "- Action: $action\n"
            ]
        ];
    }
}