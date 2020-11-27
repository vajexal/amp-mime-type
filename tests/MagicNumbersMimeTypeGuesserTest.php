<?php

namespace Vajexal\AmpMimeType\Tests;

use Vajexal\AmpMimeType\MagicNumbersMimeTypeGuesser;
use Vajexal\AmpMimeType\MimeTypeGuesser;

class MagicNumbersMimeTypeGuesserTest extends MimeTypeGuesserTest
{
    protected function getGuesser(): MimeTypeGuesser
    {
        return new MagicNumbersMimeTypeGuesser;
    }

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
//            ['tests/fixtures/doc.rtf', 'text/rtf'],
            ['tests/fixtures/doc.xls', 'application/vnd.ms-excel'],
            ['tests/fixtures/doc.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['tests/fixtures/image.gif', 'image/gif'],
//            ['tests/fixtures/image.ico', 'image/vnd.microsoft.icon'],
            ['tests/fixtures/image.jpg', 'image/jpeg'],
            ['tests/fixtures/image.png', 'image/png'],
//            ['tests/fixtures/image.svg', 'image/svg+xml'],
            ['tests/fixtures/image.tiff', 'image/tiff'],
            ['tests/fixtures/image.webp', 'image/webp'],
            ['tests/fixtures/other.zip', 'application/zip'],
            ['tests/fixtures/video.avi', 'video/x-msvideo'],
//            ['tests/fixtures/video.mov', 'video/quicktime'],
//            ['tests/fixtures/video.mp4', 'video/mp4'],
//            ['tests/fixtures/video.ogg', 'video/ogg'],
            ['tests/fixtures/video.webm', 'video/webm'],
//            ['tests/fixtures/video.wmv', 'video/x-ms-asf'],
        ];
    }
}
