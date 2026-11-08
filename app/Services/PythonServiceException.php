<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Throwable;

/** The Python ML service could not be reached or answered with an error. */
final class PythonServiceException extends RuntimeException
{
    public static function unreachable(Throwable $previous): self
    {
        return new self('The Python service could not be reached.', previous: $previous);
    }

    public static function failedResponse(int $status): self
    {
        return new self("The Python service answered with status {$status}.");
    }
}
