<?php

declare(strict_types=1);

namespace Vajexal\AmpMimeType;

use Amp\Loop;
use Amp\Promise;
use function Amp\call;

const LOOP_STATE_IDENTIFIER = MimeTypeGuesser::class;

/**
 * @param MimeTypeGuesser|null $guesser
 * @return Promise<MimeTypeGuesser>
 */
function mimeTypeGuesser(MimeTypeGuesser $guesser = null): Promise
{
    return call(function () use ($guesser) {
        if ($guesser === null) {
            $guesser = Loop::getState(LOOP_STATE_IDENTIFIER);

            if ($guesser) {
                return $guesser;
            }

            $guesser = yield createDefaultMimeTypeGuesser();
        }

        Loop::setState(LOOP_STATE_IDENTIFIER, $guesser);

        return $guesser;
    });
}

/**
 * @return Promise<MimeTypeGuesser>
 */
function createDefaultMimeTypeGuesser(): Promise
{
    return call(function () {
        $guessers = [
            new FileInfoMimeTypeGuesser,
            new FileBinaryMimeTypeGuesser,
            new MagicNumbersMimeTypeGuesser,
        ];

        /** @var MimeTypeGuesser $guesser */
        foreach ($guessers as $guesser) {
            if (yield $guesser->isSupported()) {
                return $guesser;
            }
        }

        throw MimeTypeException::noSupportedGuesser();
    });
}
