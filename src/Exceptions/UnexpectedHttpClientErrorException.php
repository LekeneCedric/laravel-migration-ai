<?php

namespace CedricLekene\LaravelMigrationAI\Exceptions;

use CedricLekene\LaravelMigrationAI\Enums\ErrorMessagesEnum;
use Exception;

class UnexpectedHttpClientErrorException extends Exception
{
    protected $message = ErrorMessagesEnum::SOMETHING_WENT_WRONG->value;
}