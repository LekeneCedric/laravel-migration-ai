<?php

namespace CedricLekene\LaravelMigrationAI\Http;

class HttpClient
{
    /**
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param mixed|null $body
     * @return array
     */
    public static function httpCall(
        string $url,
        string $method,
        array $headers = [],
        mixed $body = null
    ): array
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
            return [
                'status' => false,
                'response' => [
                    'error' => [
                        'message' => curl_error($ch)
                    ]
                ]
            ];
        }

        // Close cURL
        curl_close($ch);


        return [
            'status' => $httpCode == 200,
            'response' => json_decode($response, true)
        ];
    }
}