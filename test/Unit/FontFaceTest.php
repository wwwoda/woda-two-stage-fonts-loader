<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use Woda\WordPress\TwoStageFontsLoader\Font;
use Woda\WordPress\TwoStageFontsLoader\FontFace;

final class FontFaceTest extends AbstractTestCase
{
    private const PATH_BASE = 'https://www.woda.at/fonts';

    /** @var Font */
    private $font;

    public function setUp(): void
    {
        $this->font = new Font(
            'Open Sans',
            [
                self::PATH_BASE . '/open-sans.woff2',
                self::PATH_BASE . '/open-sans.woff',
            ],
            '700',
            true
        );
    }

    public function testGetFontFaceString(): void
    {
        self::assertSame('@font-face {font-family:"Open Sans";src:' .
            'url("https://www.woda.at/fonts/open-sans.woff2") format("woff2"),' .
            'url("https://www.woda.at/fonts/open-sans.woff") format("woff");' .
            'font-weight:700;font-style:italic;}', (new FontFace($this->font))->getFontFaceString());
    }
}
