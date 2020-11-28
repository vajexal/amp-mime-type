<?php

declare(strict_types=1);

namespace Vajexal\AmpMimeType\Tests;

use Vajexal\AmpMimeType\FileBinaryMimeTypeGuesser;
use Vajexal\AmpMimeType\MimeTypeGuesser;

class FileBinaryMimeTypeGuesserTest extends MimeTypeGuesserTest
{
    protected function getGuesser(): MimeTypeGuesser
    {
        return new FileBinaryMimeTypeGuesser;
    }

    public function testForbiddenFile()
    {
        $this->markTestSkipped();
    }
}
