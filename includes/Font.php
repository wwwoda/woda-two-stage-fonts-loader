<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

use Exception;
use InvalidArgumentException;

final class Font
{
    /** @var string */
    public $name;
    /**  @var FontFile[] */
    public $files;
    /** @var string */
    public $weight;
    /**  @var bool */
    public $italic;

    /**
     * @param string   $name   Font family name for font face.
     * @param string[] $urls   Array of font file URLs. Valid font files are 'woff2', 'woff' and 'ttf'.
     * @param string   $weight Valid weights are 'normal', 'bold', 'lighter', 'bolder', '1', '100', '100.6', '123',
     *                         '200', '300', '321', '400', '500', '600', '700', '800', '900', '1000'. Default '400'
     * @param bool     $italic True for italic font faces. Default false.
     */
    public function __construct(
        string $name,
        array $urls,
        string $weight = '400',
        bool $italic = false
    ) {
        self::checkWeight($weight);

        foreach (array_unique($urls) as $url) {
            $file = new FontFile($url);
            if (!$file->hasValidExtension()) {
                continue;
            }

            $this->files[] = $file;
        }
        $this->name = $name;
        $this->weight = $weight;
        $this->italic = $italic;
    }

    public function getPrioritizedFile(): FontFile
    {
        $file = null;
        foreach (Config::loadConfigArray('valid_extensions') as $extension) {
            $match = $this->getFileByExtension($extension);
            if ($match) {
                $file = $match;
                break;
            }
        }
        if (!$file) {
            throw new Exception(sprintf('No prioritized file found for family "%s" not found.', $this->name));
        }
        return $file;
    }

    private function getFileByExtension(string $extension): ?FontFile
    {
        $match = null;
        foreach ($this->files as $file) {
            if ($file->extension === $extension) {
                $match = $file;
                break;
            }
        }
        return $match;
    }

    private static function checkWeight(string $weight): void
    {
        $allowedWeights = [
            'normal',
            'bold',
            'lighter',
            'bolder',
            '1',
            '100',
            '100.6',
            '123',
            '200',
            '300',
            '321',
            '400',
            '500',
            '600',
            '700',
            '800',
            '900',
            '1000',
        ];
        if (! in_array($weight, $allowedWeights, true)) {
            throw new InvalidArgumentException('Font weight "' . $weight . '" is not a valid value.');
        }
    }
}
