<?php

namespace Vajexal\AmpMimeType\Tests;

use Amp\File;
use Amp\PHPUnit\AsyncTestCase;
use Vajexal\AmpMimeType\MimeTypeException;
use Vajexal\AmpMimeType\MimeTypeGuesser;

abstract class MimeTypeGuesserTest extends AsyncTestCase
{
    private MimeTypeGuesser $guesser;

    abstract protected function getGuesser(): MimeTypeGuesser;

    protected function setUpAsync()
    {
        parent::setUpAsync();

        $this->setTimeout(2000);

        $this->guesser = $this->getGuesser();

        if (!(yield $this->guesser->isSupported())) {
            $this->markTestSkipped(\sprintf('%s guesser is not supported', \get_class($this->guesser)));
        }
    }

    /**
     * overridden in @see MagicNumbersMimeTypeGuesserTest.php
     */
    public function guesserProvider(): array
    {
        return [
            ['tests/fixtures/audio.mp3', 'audio/mpeg'],
            ['tests/fixtures/audio.ogg', 'audio/ogg'],
            ['tests/fixtures/audio.wav', 'audio/x-wav'],
            ['tests/fixtures/binary', 'application/octet-stream'],
            ['tests/fixtures/doc.doc', 'application/msword'],
            ['tests/fixtures/doc.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['tests/fixtures/doc.odp', 'application/vnd.oasis.opendocument.presentation'],
            ['tests/fixtures/doc.ods', 'application/vnd.oasis.opendocument.spreadsheet'],
            ['tests/fixtures/doc.odt', 'application/vnd.oasis.opendocument.text'],
            ['tests/fixtures/doc.pdf', 'application/pdf'],
            ['tests/fixtures/doc.ppt', 'application/vnd.ms-powerpoint'],
            ['tests/fixtures/doc.rtf', 'text/rtf'],
            ['tests/fixtures/doc.xls', 'application/vnd.ms-excel'],
            ['tests/fixtures/doc.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['tests/fixtures/image.gif', 'image/gif'],
            ['tests/fixtures/image.ico', 'image/vnd.microsoft.icon'],
            ['tests/fixtures/image.jpg', 'image/jpeg'],
            ['tests/fixtures/image.png', 'image/png'],
            ['tests/fixtures/image.svg', 'image/svg+xml'],
            ['tests/fixtures/image.tiff', 'image/tiff'],
            ['tests/fixtures/image.webp', 'image/webp'],
            ['tests/fixtures/other.zip', 'application/zip'],
            ['tests/fixtures/video.avi', 'video/x-msvideo'],
            ['tests/fixtures/video.mov', 'video/quicktime'],
            ['tests/fixtures/video.mp4', 'video/mp4'],
            ['tests/fixtures/video.ogg', 'video/ogg'],
            ['tests/fixtures/video.webm', 'video/webm'],
            ['tests/fixtures/video.wmv', 'video/x-ms-asf'],
        ];
    }

    /**
     * @dataProvider guesserProvider
     */
    public function testGuesser(string $path, string $expectedMimeType)
    {
        $this->assertEquals($expectedMimeType, yield $this->guesser->guess($path));
    }

    public function invalidPathProvider(): array
    {
        return [
            ['hack.php'],
            ['tests/fixtures'],
        ];
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testInvalidPath(string $path)
    {
        $this->expectException(MimeTypeException::class);
        $this->expectExceptionMessage(\sprintf('%s is not a file', $path));

        yield $this->guesser->guess($path);
    }

    /**
     * overridden in @see FileBinaryMimeTypeGuesserTest.php
     */
    public function testForbiddenFile()
    {
        $path = 'tests/fixtures/forbidden.docx';

        $this->expectException(MimeTypeException::class);
        $this->expectExceptionMessage(\sprintf('could not detect mime type of %s', $path));

        try {
            yield File\touch($path);
            yield File\chmod($path, 0000);

            yield $this->guesser->guess($path);
        } finally {
            yield File\unlink($path);
        }
    }
}
