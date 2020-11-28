<?php

declare(strict_types=1);

namespace Vajexal\AmpMimeType;

use Amp\ByteStream\StreamException;
use Amp\File;
use Amp\File\FilesystemException;
use Amp\Promise;
use Amp\Success;
use function Amp\call;

class MagicNumbersMimeTypeGuesser implements MimeTypeGuesser
{
    public const  DEFAULT_MIME_TYPE = 'application/octet-stream';
    private const BYTES_TO_READ     = 50;

    private array $magic = [
        'epub'  =>
            [
                'mime'  =>
                    [
                        0 => 'application/epub+zip',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'jar'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/java-archive',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'class' =>
            [
                'mime'  =>
                    [
                        0 => 'application/java-vm',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'cafebabe',
                                    ],
                            ],
                    ],
            ],
        'doc'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/msword',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'd0cf11e0a1b11ae1',
                                    ],
                            ],
                    ],
            ],
        'bin'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/octet-stream',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '53503031',
                                    ],
                            ],
                    ],
            ],
        'pdf'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/pdf',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '255044462d',
                                    ],
                            ],
                    ],
            ],
        'pgp'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/pgp-encrypted',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '85',
                                    ],
                            ],
                    ],
            ],
        'ps'    =>
            [
                'mime'  =>
                    [
                        0 => 'application/postscript',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '25215053',
                                    ],
                            ],
                    ],
            ],
        'rs'    =>
            [
                'mime'  =>
                    [
                        0 => 'application/rls-services+xml',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '5253564b44415441',
                                    ],
                            ],
                    ],
            ],
        'rtf'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/rtf',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '7b5c72746631',
                                    ],
                            ],
                    ],
            ],
        'apk'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.android.package-archive',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'ez2'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.ezpix-album',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '454d5832',
                                    ],
                            ],
                    ],
            ],
        'ez3'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.ezpix-package',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '454d5533',
                                    ],
                            ],
                    ],
            ],
        'kmz'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.google-earth.kmz',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'icm'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.iccprofile',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4b434d53',
                                    ],
                            ],
                    ],
            ],
        'cab'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.ms-cab-compressed',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4d534346',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '49536328',
                                    ],
                            ],
                    ],
            ],
        'xls'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.ms-excel',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'd0cf11e0a1b11ae1',
                                    ],
                            ],
                    ],
            ],
        'chm'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.ms-htmlhelp',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '495453460300000060000000',
                                    ],
                            ],
                    ],
            ],
        'ppt'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.ms-powerpoint',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'd0cf11e0a1b11ae1',
                                    ],
                            ],
                    ],
            ],
        'mus'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.musician',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                    ],
            ],
        'odp'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.oasis.opendocument.presentation',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'ods'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.oasis.opendocument.spreadsheet',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'odt'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.oasis.opendocument.text',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'pptx'  =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'xlsx'  =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'docx'  =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'pdb'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.palm',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 17,
                                'tests'  =>
                                    [
                                        0 => '000000000000000000000000000000000000000000000000',
                                    ],
                            ],
                    ],
            ],
        'pcap'  =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.tcpdump.pcap',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'a1b2c3d4',
                                        1 => 'd4c3b2a1',
                                    ],
                            ],
                    ],
            ],
        'xar'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/vnd.xara',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '78617221',
                                    ],
                            ],
                    ],
            ],
        '7z'    =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-7z-compressed',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '377abcaf271c',
                                    ],
                            ],
                    ],
            ],
        'ace'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-ace-compressed',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '2a2a4143452a3a',
                                    ],
                            ],
                    ],
            ],
        'dmg'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-apple-diskimage',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '7801730d626260',
                                    ],
                            ],
                    ],
            ],
        'bz2'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-bzip2',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '425a68',
                                    ],
                            ],
                    ],
            ],
        'deb'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-debian-package',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '213c617263683e',
                                    ],
                            ],
                    ],
            ],
        'dir'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-director',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 8,
                                'tests'  =>
                                    [
                                        0 => '58464952',
                                    ],
                            ],
                    ],
            ],
        'dcr'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-director',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '58464952',
                                    ],
                            ],
                    ],
            ],
        'iso'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-iso9660-image',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 32769,
                                'tests'  =>
                                    [
                                        0 => '4344303031',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 34817,
                                'tests'  =>
                                    [
                                        0 => '4344303031',
                                    ],
                            ],
                        2 =>
                            [
                                'offset' => 36865,
                                'tests'  =>
                                    [
                                        0 => '4344303031',
                                    ],
                            ],
                        3 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '454d5533',
                                    ],
                            ],
                    ],
            ],
        'exe'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-msdownload',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4d5a',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '5a4d',
                                    ],
                            ],
                    ],
            ],
        'dll'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-msdownload',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4d5a',
                                    ],
                            ],
                    ],
            ],
        'com'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-msdownload',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'c9',
                                    ],
                            ],
                    ],
            ],
        'wmf'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-msmetafile',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'd7cdc69a',
                                    ],
                            ],
                    ],
            ],
        'rar'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-rar-compressed',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '526172211a0700',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '526172211a070100',
                                    ],
                            ],
                    ],
            ],
        'swf'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-shockwave-flash',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '435753',
                                        1 => '465753',
                                    ],
                            ],
                    ],
            ],
        'srt'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-subrip',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '310a3030',
                                    ],
                            ],
                    ],
            ],
        'tar'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-tar',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 257,
                                'tests'  =>
                                    [
                                        0 => '7573746172003030',
                                        1 => '7573746172202000',
                                    ],
                            ],
                    ],
            ],
        'der'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-x509-ca-cert',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '3082',
                                    ],
                            ],
                    ],
            ],
        'xpi'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-xpinstall',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'xz'    =>
            [
                'mime'  =>
                    [
                        0 => 'application/x-xz',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'fd377a585a00',
                                    ],
                            ],
                    ],
            ],
        'xml'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/xml',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '3c3f786d6c20',
                                    ],
                            ],
                    ],
            ],
        'zip'   =>
            [
                'mime'  =>
                    [
                        0 => 'application/zip',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '504b0304',
                                        1 => '504b0506',
                                        2 => '504b0708',
                                    ],
                            ],
                    ],
            ],
        'snd'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/basic',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                    ],
            ],
        'mid'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/midi',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4d546864',
                                    ],
                            ],
                    ],
            ],
        'midi'  =>
            [
                'mime'  =>
                    [
                        0 => 'audio/midi',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4d546864',
                                    ],
                            ],
                    ],
            ],
        'mp3'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/mpeg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'fffb',
                                        1 => 'fff3',
                                        2 => 'fff2',
                                    ],
                            ],
                        1 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '494433',
                                    ],
                            ],
                    ],
            ],
        'oga'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/ogg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4f676753',
                                    ],
                            ],
                    ],
            ],
        'ogg'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/ogg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4f676753',
                                    ],
                            ],
                    ],
            ],
        'aif'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-aiff',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                    ],
            ],
        'aiff'  =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-aiff',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                    ],
            ],
        'aifc'  =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-aiff',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464f524d',
                                    ],
                            ],
                    ],
            ],
        'flac'  =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-flac',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '664c6143',
                                    ],
                            ],
                    ],
            ],
        'mka'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-matroska',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '1a45dfa3',
                                    ],
                            ],
                    ],
            ],
        'wma'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-ms-wma',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '3026b2758e66cf11a6d900aa0062ce6c',
                                    ],
                            ],
                    ],
            ],
        'wav'   =>
            [
                'mime'  =>
                    [
                        0 => 'audio/x-wav',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '52494646',
                                    ],
                            ],
                    ],
            ],
        'woff'  =>
            [
                'mime'  =>
                    [
                        0 => 'font/woff',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '774f4646',
                                    ],
                            ],
                    ],
            ],
        'woff2' =>
            [
                'mime'  =>
                    [
                        0 => 'font/woff2',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '774f4632',
                                    ],
                            ],
                    ],
            ],
        'bmp'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/bmp',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '424d',
                                    ],
                            ],
                    ],
            ],
        'gif'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/gif',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '474946383761',
                                        1 => '474946383961',
                                    ],
                            ],
                    ],
            ],
        'jpeg'  =>
            [
                'mime'  =>
                    [
                        0 => 'image/jpeg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'ffd8ffdb',
                                        1 => 'ffd8ffe000104a4649460001',
                                        2 => 'ffd8ffee',
                                        3 => 'ffd8ffe1',
                                    ],
                            ],
                    ],
            ],
        'jpg'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/jpeg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => 'ffd8ffdb',
                                        1 => 'ffd8ffe000104a4649460001',
                                        2 => 'ffd8ffee',
                                        3 => 'ffd8ffe1',
                                    ],
                            ],
                    ],
            ],
        'png'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/png',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '89504e470d0a1a0a',
                                    ],
                            ],
                    ],
            ],
        'tiff'  =>
            [
                'mime'  =>
                    [
                        0 => 'image/tiff',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '49492a00',
                                        1 => '4d4d002a',
                                    ],
                            ],
                    ],
            ],
        'tif'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/tiff',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '49492a00',
                                        1 => '4d4d002a',
                                    ],
                            ],
                    ],
            ],
        'psd'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/vnd.adobe.photoshop',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '38425053',
                                    ],
                            ],
                    ],
            ],
        'djvu'  =>
            [
                'mime'  =>
                    [
                        0 => 'image/vnd.djvu',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '41542654464f524d',
                                    ],
                            ],
                    ],
            ],
        'djv'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/vnd.djvu',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '41542654464f524d',
                                    ],
                            ],
                    ],
            ],
        'webp'  =>
            [
                'mime'  =>
                    [
                        0 => 'image/webp',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '52494646',
                                    ],
                            ],
                    ],
            ],
        'ico'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/x-icon',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '00000100',
                                    ],
                            ],
                    ],
            ],
        'pic'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/x-pict',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '00',
                                    ],
                            ],
                    ],
            ],
        'pbm'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/x-portable-bitmap',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '50310a',
                                    ],
                            ],
                    ],
            ],
        'pgm'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/x-portable-graymap',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '50320a',
                                    ],
                            ],
                    ],
            ],
        'ppm'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/x-portable-pixmap',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '50330a',
                                    ],
                            ],
                    ],
            ],
        'xpm'   =>
            [
                'mime'  =>
                    [
                        0 => 'image/x-xpixmap',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '2f2a2058504d202a2f',
                                    ],
                            ],
                    ],
            ],
        'eml'   =>
            [
                'mime'  =>
                    [
                        0 => 'message/rfc822',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '5265636569766564',
                                    ],
                            ],
                    ],
            ],
        'tsv'   =>
            [
                'mime'  =>
                    [
                        0 => 'text/tab-separated-values',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '47',
                                    ],
                            ],
                    ],
            ],
        '3gp'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/3gpp',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 4,
                                'tests'  =>
                                    [
                                        0 => '667479703367',
                                    ],
                            ],
                    ],
            ],
        '3g2'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/3gpp2',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 4,
                                'tests'  =>
                                    [
                                        0 => '667479703367',
                                    ],
                            ],
                    ],
            ],
        'mp4'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/mp4',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 4,
                                'tests'  =>
                                    [
                                        0 => '000000186674797069736f6d',
                                    ],
                            ],
                    ],
            ],
        'mpeg'  =>
            [
                'mime'  =>
                    [
                        0 => 'video/mpeg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '000001ba',
                                        1 => '47',
                                        2 => '000001b3',
                                    ],
                            ],
                    ],
            ],
        'mpg'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/mpeg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '000001ba',
                                        1 => '47',
                                        2 => '000001b3',
                                    ],
                            ],
                    ],
            ],
        'ogv'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/ogg',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '4f676753',
                                    ],
                            ],
                    ],
            ],
        'webm'  =>
            [
                'mime'  =>
                    [
                        0 => 'video/webm',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '1a45dfa3',
                                    ],
                            ],
                    ],
            ],
        'flv'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-flv',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '464c56',
                                    ],
                            ],
                    ],
            ],
        'mkv'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-matroska',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '1a45dfa3',
                                    ],
                            ],
                    ],
            ],
        'mk3d'  =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-matroska',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '1a45dfa3',
                                    ],
                            ],
                    ],
            ],
        'mks'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-matroska',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '1a45dfa3',
                                    ],
                            ],
                    ],
            ],
        'asf'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-ms-asf',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '3026b2758e66cf11a6d900aa0062ce6c',
                                    ],
                            ],
                    ],
            ],
        'vob'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-ms-vob',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '000001ba',
                                    ],
                            ],
                    ],
            ],
        'wmv'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-ms-wmv',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '3026b2758e66cf11a6d900aa0062ce6c',
                                    ],
                            ],
                    ],
            ],
        'avi'   =>
            [
                'mime'  =>
                    [
                        0 => 'video/x-msvideo',
                    ],
                'tests' =>
                    [
                        0 =>
                            [
                                'offset' => 0,
                                'tests'  =>
                                    [
                                        0 => '52494646',
                                    ],
                            ],
                    ],
            ],
    ];

    public function isSupported(): Promise
    {
        return new Success(true);
    }

    public function guess(string $path): Promise
    {
        return call(function () use ($path) {
            try {
                if (!(yield File\isfile($path))) {
                    throw MimeTypeException::invalidPath($path);
                }

                $ext = \pathinfo($path, PATHINFO_EXTENSION);

                if (!$ext || empty($this->magic[$ext])) {
                    return self::DEFAULT_MIME_TYPE;
                }

                /** @var File\File $file */
                $file = yield File\open($path, 'rb');

                foreach ($this->magic[$ext]['tests'] as $tests) {
                    yield $file->seek($tests['offset']);

                    $bytes = yield $file->read(self::BYTES_TO_READ);
                    $bytes = \bin2hex($bytes);

                    foreach ($tests['tests'] as $test) {
                        if (str_starts_with($bytes, $test)) {
                            return $this->magic[$ext]['mime'][0];
                        }
                    }
                }

                return self::DEFAULT_MIME_TYPE;
            } catch (FilesystemException | StreamException $e) {
                throw MimeTypeException::couldNotDetect($path, $e);
            }
        });
    }
}
