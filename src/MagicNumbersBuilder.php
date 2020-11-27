<?php

namespace Vajexal\AmpMimeType;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Symfony\Component\DomCrawler\Crawler;
use function Amp\call;

class MagicNumbersBuilder
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function build(): Promise
    {
        return call(function () {
            [$extToMime, $magicNumbers] = yield [$this->getExtToMimeMapping(), $this->getMagicNumbersMapping()];

            return \array_reduce(\array_keys($extToMime), function ($carry, $ext) use ($extToMime, $magicNumbers) {
                if (isset($magicNumbers[$ext])) {
                    $carry[$ext] = [
                        'mime'  => $extToMime[$ext],
                        'tests' => $magicNumbers[$ext],
                    ];
                }

                return $carry;
            });
        });
    }

    private function getExtToMimeMapping(): Promise
    {
        return call(function () {
            /** @var Response $response */
            $response  = yield $this->httpClient->request(new Request('https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'));
            $mimeTypes = yield $response->getBody()->buffer();

            $lines = \explode(PHP_EOL, $mimeTypes);

            return \array_reduce($lines, function ($mimes, $line) {
                if (!$line || str_starts_with($line, '#')) {
                    return $mimes;
                }

                if (!\preg_match('/^([\w\-\/.+]+)\s+(.+)$/', $line, $matches)) {
                    \trigger_error(\sprintf('invalid line %s', $line), E_USER_WARNING);

                    return $mimes;
                }

                [, $mimeType, $extensions] = $matches;
                $extensions = \explode(' ', $extensions);

                foreach ($extensions as $extension) {
                    $extension = \mb_strtolower($extension);

                    if (!isset($mimes[$extension])) {
                        $mimes[$extension] = [];
                    }

                    $mimes[$extension][] = \mb_strtolower($mimeType);
                }

                return $mimes;
            }, []);
        });
    }

    private function getMagicNumbersMapping(): Promise
    {
        return call(function () {
            /** @var Response $response */
            $response     = yield $this->httpClient->request(new Request('https://en.wikipedia.org/wiki/List_of_file_signatures'));
            $magicNumbers = yield $response->getBody()->buffer();

            $crawler = new Crawler($magicNumbers);

            $table = $crawler->filter('table a[title="Filename extension"]')->first()->closest('table');

            $mimes = $table->filter('tr')->slice(1)->each(function (Crawler $line) {
                $columns = $line->children('td');

                $hex     = $this->findHexCodes($columns->eq(0));
                $offsets = $this->findOffsets($columns->eq(2));
                $ext     = $this->findExtensions($columns->eq(3));

                if (\in_array('ts', $ext, true)) {
                    $offsets = [0];
                }

                return [
                    'hex'    => $hex,
                    'offset' => $offsets,
                    'ext'    => $ext,
                ];
            });

            return \array_reduce($mimes, function ($mimes, $mime) {
                if (!$mime['ext']) {
                    return $mimes;
                }

                $tests = \array_map(fn ($offset) => [
                    'offset' => $offset,
                    'tests'  => $mime['hex'],
                ], $mime['offset']);

                foreach ($mime['ext'] as $ext) {
                    if (!isset($mimes[$ext])) {
                        $mimes[$ext] = [];
                    }

                    $mimes[$ext] = \array_merge($mimes[$ext], $tests);
                }

                return $mimes;
            }, []);
        });
    }

    private function findHexCodes(Crawler $tableCell): array
    {
        $hex = $tableCell->children('pre, code')->each(fn (Crawler $node) => $node->text());
        $hex = \str_replace("\u{00a0}", ' ', $hex);
        $hex = \str_replace(' ', '', $hex);

        return \array_map(function (string $test) {
            $test = \mb_strtolower(\trim($test));

            if (\preg_match('/^([\w ]+)\?\?/', $test, $matches)) {
                return \rtrim($matches[1]);
            }

            return $test;
        }, $hex);
    }

    private function findOffsets(Crawler $tableCell): array
    {
        $lines = $this->getMultilineText($tableCell);

        $offsets = \array_filter($lines, fn ($offset) => $offset !== 'any');

        return \array_map(fn ($offset) => @\hexdec($offset), $offsets);
    }

    private function findExtensions(Crawler $tableCell): array
    {
        $lines = $this->getMultilineText($tableCell);

        return \array_map(fn ($ext) => \mb_strtolower($ext), $lines);
    }

    private function getMultilineText(Crawler $node): array
    {
        $content = \preg_replace('/<\w+(\s*)?\/?>/i', PHP_EOL, $node->html());
        $content = \strip_tags($content);
        $lines   = \array_map(fn ($line) => \trim($line), \explode(PHP_EOL, $content));
        $lines   = \array_filter($lines, fn ($line) => $line !== '');
        return \array_values($lines);
    }
}
