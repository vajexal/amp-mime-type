<?php

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
     */
    public function guess(string $path): Promise;
}
