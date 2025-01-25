<?php

namespace CedricLekene\LaravelMigrationAI\Enums;

enum ErrorMessagesEnum: string
{
    case UNKNOWN_API_KEY_PROVIDED = 'Please provide an API key for Gemini( GEMINI_API_KEY= ) in your .env file';
    case SOMETHING_WENT_WRONG = 'Something went wrong ! check your API key and Try again !';
}
