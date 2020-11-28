<?php

declare(strict_types=1);

namespace Vajexal\AmpMimeType;

use Exception;
use Throwable;

class MimeTypeException extends Exception
{
    const INVALID_PATH_CODE     = 0;
    const COULD_NOT_DETECT_CODE = 1;
    const NO_SUPPORTED_GUESSER  = 2;

    public static function invalidPath(string $path): self
    {
        return new self(\sprintf('%s is not a file', $path), self::INVALID_PATH_CODE);
    }

    public static function couldNotDetect(string $path, Throwable $previous = null): self
    {
        return new self(\sprintf('could not detect mime type of %s', $path), self::COULD_NOT_DETECT_CODE, $previous);
    }

    public static function noSupportedGuesser(): self
    {
        return new self('There is no supported mime type guesser', self::NO_SUPPORTED_GUESSER);
    }
}
