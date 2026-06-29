<?php

namespace App\Exceptions;

class ServiceUnavailableException extends ApiException
{
    // Thrown when API returns 503 or 504 Status Code
}
