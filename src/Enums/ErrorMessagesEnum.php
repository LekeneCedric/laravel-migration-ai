<?php

namespace CedricLekene\LaravelMigrationAI\Enums;

enum ErrorMessagesEnum: string
{

    case UNKNOWN_API_KEY_PROVIDED = 'Please provide an API key for Gemini or OpenAI in your .env file';
    case SOMETHING_WENT_WRONG = 'Something went wrong. The response is something. Try again !';
}
