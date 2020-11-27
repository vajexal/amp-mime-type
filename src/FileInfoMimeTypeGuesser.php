<?php

namespace Vajexal\AmpMimeType;

use Amp\File;
use Amp\Promise;
use Amp\Success;
use function Amp\call;
use function Amp\ParallelFunctions\parallel;

class FileInfoMimeTypeGuesser implements MimeTypeGuesser
{
    public function isSupported(): Promise
    {
        return new Success(
            \function_exists('Amp\ParallelFunctions\parallel') && \function_exists('mime_content_type')
        );
    }

    public function guess(string $path): Promise
    {
        return call(function () use ($path) {
            if (!(yield File\isfile($path))) {
                throw MimeTypeException::invalidPath($path);
            }

            $guesser = parallel(fn ($path) => @\mime_content_type($path));

            $mimeType = yield $guesser($path);

            if ($mimeType === false) {
                throw MimeTypeException::couldNotDetect($path);
            }

            return $mimeType;
        });
    }
}
