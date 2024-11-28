<?php

namespace CedricLekene\LaravelMigrationAI;

use Illuminate\Support\ServiceProvider;

class LaravelMigrationAIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
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
            ]);
        }
    }
}