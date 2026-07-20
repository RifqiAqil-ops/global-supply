<?php

namespace App\Http\Clients;

use App\Exceptions\ApiException;
use App\Exceptions\InvalidResponseException;
use App\Exceptions\NetworkException;
use App\Exceptions\RateLimitException;
use App\Exceptions\ServiceUnavailableException;
use App\Models\ApiLog;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseApiClient
{
    protected string $baseUrl;
    protected string $providerName;

    public function __construct(string $baseUrl, string $providerName)
    {
        $this->baseUrl = $baseUrl;
        $this->providerName = $providerName;
    }

    /**
     * Send an HTTP request and handle retries, caching, logging, and error mapping.
     *
     * @param string $method GET, POST, etc.
     * @param string $endpoint Endpoint path relative to base URL
     * @param array $options Query params, headers, payload, etc.
     * @return array Decoded JSON response
     * 
     * @throws ApiException
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $method = strtoupper($method);

        // Fetch configurations from gscrip.php config
        $timeout = config('gscrip.options.timeout', 10);
        $retryAttempts = config('gscrip.options.retry', 3);
        $retryDelay = config('gscrip.options.retry_delay', 100);
        $userAgent = config('gscrip.options.user_agent');

        $headers = array_merge([
            'User-Agent' => $userAgent,
            'Accept' => 'application/json',
        ], $options['headers'] ?? []);

        $queryParams = $options['query'] ?? [];
        $payload = $options['json'] ?? [];

        $startTime = microtime(true);
        $statusCode = null;
        $errorMessage = null;
        $responseSize = 0;
        $isSuccess = false;

        try {
            // Build the HTTP request client instance
            $client = Http::withHeaders($headers)->timeout($timeout);

            if ($retryAttempts > 0) {
                $client->retry($retryAttempts, $retryDelay, function (Throwable $exception, $request) {
                    return $exception instanceof ConnectionException;
                });
            }

            // Perform the request
            /** @var Response $response */
            $response = match ($method) {
                'POST' => $client->post($url, $payload),
                'PUT' => $client->put($url, $payload),
                'DELETE' => $client->delete($url, $queryParams),
                default => $client->get($url, $queryParams),
            };

            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseSize = strlen($responseBody);

            // Log entire response API
            Log::info("API Response [{$this->providerName}]: Status={$statusCode}, URL={$url}, Body={$responseBody}");
            
            // Evaluate status
            if ($response->successful()) {
                $isSuccess = true;
                $decoded = $response->json();
                
                if ($decoded === null && $responseBody !== '') {
                    throw new InvalidResponseException(
                        message: "Malformed JSON response received from API provider.",
                        provider: $this->providerName,
                        statusCode: $statusCode,
                        context: ['raw_body' => substr($responseBody, 0, 500)]
                    );
                }
                
                return $decoded ?? [];
            }

            // Log failed request details
            Log::error("API Request Failed [{$this->providerName}]: Status={$statusCode}, URL={$url}, Body={$responseBody}");

            // Handle API Errors based on HTTP Status Codes
            $this->handleHttpError($statusCode, $responseBody);

        } catch (ConnectionException $e) {
            $errorMessage = "Network or connection timeout error: " . $e->getMessage();
            throw new NetworkException(
                message: $errorMessage,
                provider: $this->providerName,
                statusCode: 0,
                context: ['url' => $url, 'params' => $queryParams],
                previous: $e
            );
        } catch (ApiException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = $e->getStatusCode();
            throw $e;
        } catch (Throwable $e) {
            $errorMessage = "Unexpected integration error: " . $e->getMessage();
            throw new ApiException(
                message: $errorMessage,
                provider: $this->providerName,
                statusCode: $statusCode ?? 500,
                context: ['url' => $url],
                previous: $e
            );
        } finally {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // Milliseconds

            // Log API interaction in database
            $this->logApiCall(
                method: $method,
                endpoint: $url,
                statusCode: $statusCode,
                responseTime: $responseTime,
                responseSize: $responseSize,
                params: array_merge($queryParams, $payload),
                isSuccess: $isSuccess,
                errorMessage: $errorMessage
            );
        }

        return [];
    }

    /**
     * Maps HTTP status codes into specific typed exceptions.
     */
    private function handleHttpError(int $statusCode, string $responseBody): void
    {
        $data = json_decode($responseBody, true) ?? [];
        $message = $data['message'] ?? $data['error'] ?? "API request failed";
        $fullMsg = "[HTTP Status: {$statusCode}] Message: {$message}. Response Body: {$responseBody}";

        match ($statusCode) {
            429 => throw new RateLimitException(
                message: "API Rate Limit Exceeded: " . $fullMsg,
                provider: $this->providerName,
                statusCode: $statusCode,
                context: ['response' => $data]
            ),
            503, 504 => throw new ServiceUnavailableException(
                message: "External Service Temporary Down: " . $fullMsg,
                provider: $this->providerName,
                statusCode: $statusCode,
                context: ['response' => $data]
            ),
            default => throw new ApiException(
                message: "API error response: " . $fullMsg,
                provider: $this->providerName,
                statusCode: $statusCode,
                context: ['response' => $data]
            )
        };
    }

    /**
     * Record request log to database.
     */
    protected function logApiCall(
        string $method,
        string $endpoint,
        ?int $statusCode,
        float $responseTime,
        int $responseSize,
        array $params,
        bool $isSuccess,
        ?string $errorMessage
    ): void {
        try {
            ApiLog::create([
                'provider' => $this->providerName,
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => $statusCode,
                'response_time' => $responseTime,
                'request_params' => $params,
                'response_size' => $responseSize,
                'error_message' => $errorMessage,
                'is_success' => $isSuccess,
                'called_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Fallback to local Laravel log to prevent breaking system if DB write fails
            Log::error("Failed to write API logs in DB: " . $e->getMessage(), [
                'provider' => $this->providerName,
                'endpoint' => $endpoint,
                'error_message' => $errorMessage
            ]);
        }
    }
}
