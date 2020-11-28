<?php

declare(strict_types=1);

namespace Vajexal\AmpMimeType\Tests;

use Vajexal\AmpMimeType\FileInfoMimeTypeGuesser;
use Vajexal\AmpMimeType\MimeTypeGuesser;

class FileInfoMimeTypeGuesserTest extends MimeTypeGuesserTest
{
    protected function getGuesser(): MimeTypeGuesser
    {
        return new FileInfoMimeTypeGuesser;
    }
}
