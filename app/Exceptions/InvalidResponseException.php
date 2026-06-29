<?php

namespace App\Exceptions;

class InvalidResponseException extends ApiException
{
    // Thrown when API returns malformed JSON or response verification checks fail
}
