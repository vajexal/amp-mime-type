<?php

namespace Vajexal\AmpMimeType;

use Amp\File;
use Amp\Process\Process;
use Amp\Promise;
use function Amp\ByteStream\buffer;
use function Amp\call;

class FileBinaryMimeTypeGuesser implements MimeTypeGuesser
{
    public function isSupported(): Promise
    {
        return call(function () {
            if (!\class_exists('Amp\Process\Process')) {
                return false;
            }

            $process = new Process(['file', '-v']);

            yield $process->start();

            $exitCode = yield $process->join();

            return $exitCode === 0;
        });
    }

    public function guess(string $path): Promise
    {
        return call(function () use ($path) {
            if (!(yield File\isfile($path))) {
                throw MimeTypeException::invalidPath($path);
            }

            $process = new Process(['file', '-b', '--mime-type', $path]);

            yield $process->start();

            $mimeType = yield buffer($process->getStdout());

            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                throw MimeTypeException::couldNotDetect($path);
            }

            return \trim($mimeType);
        });
    }
}
