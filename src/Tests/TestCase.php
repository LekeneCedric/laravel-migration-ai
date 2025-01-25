<?php

namespace CedricLekene\LaravelMigrationAI\Tests;
use CedricLekene\LaravelMigrationAI\Enums\EnvironmentVariablesEnum;
use CedricLekene\LaravelMigrationAI\LaravelMigrationAIServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestcase;
class TestCase extends OrchestraTestcase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_ENV[EnvironmentVariablesEnum::GEMINI_API_KEY->value] = '';
        $_SERVER['REQUEST_URI'] = '/some-uri';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelMigrationAIServiceProvider::class
        ];
    }
}