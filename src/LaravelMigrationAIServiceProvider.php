<?php

namespace CedricLekene\LaravelMigrationAI;

use CedricLekene\LaravelMigrationAI\Database\Console\Migrations\MakeMigrationAICommand;
use CedricLekene\LaravelMigrationAI\Database\Migrations\MigrationAICreator;
use Illuminate\Support\ServiceProvider;

class LaravelMigrationAIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerCreator();
        $this->registerCommands();
    }

    /**
     * Register the console commands for the package.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeMigrationAICommand::class
            ]);
        }
    }

    private function registerCreator(): void
    {
        $this->app->singleton(MigrationAICreator::class, function($app) {
            return new MigrationAICreator($app['files'],  __DIR__ . '/Database/Migrations/stubs');
        });
    }
}