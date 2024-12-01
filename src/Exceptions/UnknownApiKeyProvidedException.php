<?php

namespace CedricLekene\LaravelMigrationAI\Exceptions;

use CedricLekene\LaravelMigrationAI\Enums\ErrorMessagesEnum;
use Exception;

class UnknownApiKeyProvidedException extends Exception
{
    protected $message = ErrorMessagesEnum::UNKNOWN_API_KEY_PROVIDED->value;
}