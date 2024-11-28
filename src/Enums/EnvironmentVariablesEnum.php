<?php

namespace CedricLekene\LaravelMigrationAI\Enums;

enum EnvironmentVariablesEnum: string
{
    case GEMINI_API_KEY = 'GEMINI_API_KEY';
    case OPENAI_API_KEY = 'OPENAI_API_KEY';
    case GEMINI_MODEL = 'GEMINI_MODEL';
    case DEFAULT_GEMINI_MODEL = 'gemini-1.5-flash';
    case OPENAI_MODEL = 'OPENAI_MODEL';
    case DEFAULT_OPENAI_MODEL = 'gpt-4o';
}
