<?php

namespace CedricLekene\LaravelMigrationAI\Dto;

class MigrationContentDto
{
    public function __construct(
        public string $message = '',
        public string $migrationUp = '',
        public string $migrationDown = '',
    )
    {
    }
}