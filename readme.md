Mime type guessing for [amphp](https://amphp.org)

### Installation

```bash
composer require vajexal/amp-mime-type:dev-master
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
