<?php

declare(strict_types=1);

namespace Vajexal\AmpMimeType;

use Amp\Promise;

interface MimeTypeGuesser
{
    /**
     * @return Promise<bool>
     */
    public function isSupported(): Promise;

    /**
     * @param string $path
     * @return Promise<string>
     * @throws MimeTypeException
     */
    public function guess(string $path): Promise;
}
