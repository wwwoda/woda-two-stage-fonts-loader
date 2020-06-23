<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use Woda\WordPress\TwoStageFontsLoader\Font;
use Woda\WordPress\TwoStageFontsLoader\FontSpecification;

final class FontSpecificationTest extends AbstractTestCase
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

    public function testGetFontSpecificationString(): void
    {
        self::assertSame(
            "'italic 700 1em Open Sans'",
            (new FontSpecification($this->font))->getFontSpecificationString()
        );
    }
}
