<?php

namespace Woda\WordPress\TwoStageFontsLoader;

use Woda\WordPress\TwoStageFontsLoader\Utils\Error;

final class Font
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $filename;
    /**
     * @var string
     */
    public $weight;
    /**
     * @var bool
     */
    public $italic;
    /**
     * @var array
     */
    public $extensions;

    /**
     * Font constructor.
     * @param string $name
     * @param string $filename
     * @param string $weight
     * @param bool $italic
     * @param array $extensions
     */
    public function __construct(
        string $name,
        string $filename,
        string $weight = '400',
        bool $italic = false,
        array $extensions =['woff2', 'woff']
    )
    {
        self::checkName($name);
        self::checkFilename($filename);
        self::checkWeight($weight);
        self::checkExtensions($extensions);

        $this->name = $name;
        $this->filename = $filename;
        $this->weight = $weight;
        $this->italic = $italic;
        $this->extensions = $extensions;
    }

    /**
     * @param string $name
     */
    private static function checkName(string $name): void
    {
        if (empty($name)) {
            Error::notice('Font name must not be empty.');
        }
    }

    /**
     * @param string $filename
     */
    private static function checkFilename(string $filename): void
    {
        if (empty($filename)) {
            Error::notice('Font file name must not be empty.');
        }
    }

    /**
     * @param array $extensions
     */
    private static function checkExtensions(array $extensions): void
    {
        $allowedExtensions = [
            'ttf',
            'woff',
            'woff2'
        ];
        if (count(array_intersect($extensions, $allowedExtensions)) < 1) {
            Error::notice('Font extensions array needs to contain either woff, woff2, ttf or any combination of these.');
        }
        if (count(array_diff($extensions, $allowedExtensions)) < 0) {
            Error::notice('Font extensions array contains unkonwn extensions (allowed: ttf, woff, woff2).');
        }
    }

    /**
     * @param string $weight
     */
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
            '1000'
        ];
        if (! in_array($weight, $allowedWeights, true)) {
            Error::notice('Font weight "' . $weight . '" is not a valid value.');
        }
    }
}
