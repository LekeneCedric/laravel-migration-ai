<?php

namespace CedricLekene\LaravelMigrationAI\contracts;

use CedricLekene\LaravelMigrationAI\Dto\MigrationContentDto;

interface AIService
{
    /**
     * Contract for the AI services
     * @param string $apiKey
     * @param string $model
     * @param bool $isCreate
     * @param string $description
     * @return MigrationContentDto
     */
    public function execute(string $apiKey, string $model, bool $isCreate, string $description): MigrationContentDto;
}