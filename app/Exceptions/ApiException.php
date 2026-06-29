<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ApiException extends Exception
{
    protected ?string $provider;
    protected ?int $statusCode;
    protected array $context;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?string $provider = null,
        ?int $statusCode = null,
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->provider = $provider;
        $this->statusCode = $statusCode;
        $this->context = $context;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
