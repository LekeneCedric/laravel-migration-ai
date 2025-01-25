<?php

namespace CedricLekene\LaravelMigrationAI\Tests\E2e;

use CedricLekene\LaravelMigrationAI\Tests\TestCase;

class CreateMigrationAITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_migration_contents()
    {
        $this->artisan('make:migration-ai create_users_table 
                description="create users table with field name, and category_card (enum[card|bank])"
            ')->assertExitCode(0);
    }

}