<?php

namespace CedricLekene\LaravelMigrationAI\Infrastructure;

class HttpClient
{
    public static function httpCall(
        string $url,
        string $method,
        array $headers = [],
        mixed $body = null
    )
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        // Set headers if provided
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Set the request body if provided
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // Check for errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return false; // Return false on cURL error
        }

        // Close cURL
        curl_close($ch);

        // Return data if successful (HTTP 200), otherwise return false
        return ($httpCode === 200) ? json_decode($response, true) : false;
    }
}