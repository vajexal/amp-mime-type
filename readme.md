Mime type guessing for [amphp](https://amphp.org)

[![Build Status](https://github.com/vajexal/amp-mime-type/workflows/Build/badge.svg)](https://github.com/vajexal/amp-mime-type/actions)

### Installation

```bash
composer require vajexal/amp-mime-type
```

### Usage

```php
<?php

declare(strict_types=1);

use Amp\Loop;
use Vajexal\AmpMimeType\MimeTypeGuesser;
use function Vajexal\AmpMimeType\mimeTypeGuesser;

require_once 'vendor/autoload.php';

Loop::run(function () {
    /** @var MimeTypeGuesser $guesser */
    $guesser = yield mimeTypeGuesser();

    echo yield $guesser->guess('image.png'), PHP_EOL;
});
```

#### Guessers

- [FileInfoMimeTypeGuesser](src/FileInfoMimeTypeGuesser.php) - using [mime_content_type](https://www.php.net/manual/en/function.mime-content-type.php) function
- [FileBinaryMimeTypeGuesser](src/FileBinaryMimeTypeGuesser.php) - using [file](https://www.man7.org/linux/man-pages/man1/file.1.html) command
- [MagicNumbersMimeTypeGuesser](src/MagicNumbersMimeTypeGuesser.php) - using [magic numbers](https://en.wikipedia.org/wiki/List_of_file_signatures) detection
