<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use Woda\WordPress\TwoStageFontsLoader\Font;
use Woda\WordPress\TwoStageFontsLoader\Preloader;

final class PreloaderTest extends AbstractTestCase
{
    private const PATH_BASE = 'https://www.woda.at/fonts';

    /** @var Font */
    private $font;

    public function setUp(): void
    {
        $this->font = new Font(
            'Open Sans',
            [
                self::PATH_BASE . '/open-sans.ttf',
                self::PATH_BASE . '/open-sans.woff',
                self::PATH_BASE . '/open-sans.woff2',
            ],
            '700',
            true
        );
    }

    public function testGetPreloaderString(): void
    {
        self::assertSame('<link rel="preload" href="https://www.woda.at/fonts/open-sans.woff2" as="font" ' .
            'type="font/woff2" crossorigin>', (new Preloader($this->font))->getPreloaderString());
    }
}
