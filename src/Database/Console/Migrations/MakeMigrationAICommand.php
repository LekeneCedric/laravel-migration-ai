<?php

namespace CedricLekene\LaravelMigrationAI\Database\Console\Migrations;


use CedricLekene\LaravelMigrationAI\Database\Migrations\MigrationAICreator;
use CedricLekene\LaravelMigrationAI\Dto\MigrationContentDto;
use CedricLekene\LaravelMigrationAI\Enums\EnvironmentVariablesEnum;
use CedricLekene\LaravelMigrationAI\Enums\ServiceTypeEnum;
use CedricLekene\LaravelMigrationAI\Exceptions\UnexpectedHttpClientErrorException;
use CedricLekene\LaravelMigrationAI\Exceptions\UnknownApiKeyProvidedException;
use CedricLekene\LaravelMigrationAI\Factory\AIServiceFactory;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class MakeMigrationAICommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:migration-ai {name : The name of the migration}
        {description : The description of the migration needs}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file assisted by AI';

    /**
     * The migration creator instance.
     *
     * @var MigrationAICreator
     */
    protected MigrationAICreator $creator;

    public function __construct(MigrationAICreator $creator)
    {
        parent::__construct();
        $this->creator = $creator;
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        try {
            // It's possible for the developer to specify the tables to modify in this
            // schema operation. The developer may also specify if this table needs
            // to be freshly created so we can create the appropriate migrations.
            $name = Str::snake(trim($this->input->getArgument('name')));

            $table = $this->input->getOption('table');

            $create = $this->input->getOption('create') ?: false;

            $description = $this->input->getArgument('description');
            $content = $this->generateMigrationContent($description, $create);
            if (!$content) {
                return 1;
            }

            // If no table was given as an option but a create option is given then we
            // will use the "create" option as the table name. This allows the devs
            // to pass a table name into this option as a short-cut for creating.
            if (!$table && is_string($create)) {
                $table = $create;

                $create = true;
            }

            // Next, we will attempt to guess the table name if this the migration has
            // "create" in the name. This will allow us to provide a convenient way
            // of creating migrations that create new tables for the Application.
            if (!$table) {
                [$table, $create] = TableGuesser::guess($name);
            }

            $this->writeMigration(
                name: $name,
                table: $table,
                create: $create,
                content: $content
            );
        } catch (Exception $e) {
            $this->components->error($e->getMessage());
            return CommandAlias::FAILURE;
        }
        return CommandAlias::SUCCESS;
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $table
     * @param bool $create
     * @param MigrationContentDto $content
     * @return void
     * @throws FileNotFoundException
     */
    private function writeMigration(string $name, string $table, bool $create, MigrationContentDto $content): void
    {
        $file = $this->creator->create(
            name: $name,
            path: $this->getMigrationPath(),
            table: $table,
            create: $create,
            content: $content
        );
        $this->components->info(sprintf('Migration [%s] created successfully.', $file));
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        if (!is_null($targetPath = $this->input->getOption('path'))) {
            return !$this->usingRealPath()
                ? $this->laravel->basePath() . '/' . $targetPath
                : $targetPath;
        }

        return parent::getMigrationPath();
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => ['What should the migration be named?', 'E.g. create_flights_table'],
            'description' => ['what is the description of the migration?', 'E.g. With fields: name(required, maxLength 300), email, password'],
        ];
    }

    /**
     * Generate migration content using AI service
     *
     * @param string $description
     * @param bool $isCreate
     * @return MigrationContentDto|null
     * @throws UnexpectedHttpClientErrorException
     * @throws UnknownApiKeyProvidedException
     */
    private function generateMigrationContent(string $description, bool $isCreate): ?MigrationContentDto
    {
        $geminiIsEnabled = env(EnvironmentVariablesEnum::GEMINI_API_KEY->value);
        $apiKey = $this->getApiKey($geminiIsEnabled);
        $model = $this->getAIModel($geminiIsEnabled);
        $serviceType = $geminiIsEnabled ? ServiceTypeEnum::GEMINI : ServiceTypeEnum::OPENAI;
        if (!$apiKey) {
            throw new UnknownApiKeyProvidedException();
        }
        $this->info('processing .....');
        $response = (AIServiceFactory::make($serviceType))->execute(
            apiKey: $apiKey,
            model: $model,
            isCreate: $isCreate,
            description: $description,
        );
        if ($response->message) {
            throw new UnexpectedHttpClientErrorException();
        }
        return $response;
    }

    /**
     * Get the API key to use
     *
     * @param bool $geminiIsEnabled
     * @return mixed
     */
    public function getApiKey(bool $geminiIsEnabled): mixed
    {
        return $geminiIsEnabled
            ? env(EnvironmentVariablesEnum::GEMINI_API_KEY->value)
            : env(EnvironmentVariablesEnum::OPENAI_API_KEY->value);
    }

    /**
     * Get the AI model to use
     *
     * @param bool $geminiIsEnabled
     * @return mixed
     */
    public function getAIModel(bool $geminiIsEnabled): mixed
    {
        return $geminiIsEnabled
            ? (env(EnvironmentVariablesEnum::GEMINI_MODEL->value) ?? EnvironmentVariablesEnum::DEFAULT_GEMINI_MODEL->value)
            : (env(EnvironmentVariablesEnum::OPENAI_MODEL->value) ?? EnvironmentVariablesEnum::DEFAULT_OPENAI_MODEL->value);
    }
}