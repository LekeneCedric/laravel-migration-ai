<?php

namespace CedricLekene\LaravelMigrationAI\Database\Migrations;

use CedricLekene\LaravelMigrationAI\Dto\MigrationContentDto;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;

class MigrationAICreator extends MigrationCreator
{
    public function __construct(Filesystem $files, $customStubPath)
    {
        parent::__construct($files, $customStubPath);
    }

    /**
     * Create a new migration at the given path.
     *
     * @param string $name
     * @param string $path
     * @param null $table
     * @param bool $create
     * @param MigrationContentDto|null $content
     * @return string
     *
     * @throws FileNotFoundException
     */
    public function create($name, $path, $table = null, $create = false, MigrationContentDto $content = null): string
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path, $this->populateStub($stub, $table, $create, $content)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table, $path);

        return $path;
    }

    /**
     * Get the migration stub file.
     *
     * @param string|null $table
     * @param bool $create
     * @return string
     * @throws FileNotFoundException
     */
    protected function getStub($table, $create): string
    {
        if ($create) {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.create.stub')
                ? $customPath
                : $this->stubPath().'/migration.create.stub';
        } else {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.update.stub')
                ? $customPath
                : $this->stubPath().'/migration.update.stub';
        }

        return $this->files->get($stub);
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param string $stub
     * @param string|null $table
     * @param null $create
     * @param MigrationContentDto|null $content
     * @return string
     */
    public function populateStub($stub, $table, $create = null, MigrationContentDto $content = null): string
    {
        $migrationUp = str_replace(";", ";\n\t\t\t", $content->migrationUp);
        $migrationDown = str_replace(";", ";\n\t\t\t", $content->migrationDown ?? ' ');

        if ($create) {
            $stub = str_replace(
                ['{{ table }}', '{{ content }}'],
                [$table, $migrationUp],
                $stub
            );
        }
        if (!$create) {
            $stub = str_replace(
                ['{{ table }}', '{{ content }}', '{{ reverse_content }}'],
                [$table, $migrationUp, $migrationDown],
                $stub
            );
        }
        return $stub;
    }
}