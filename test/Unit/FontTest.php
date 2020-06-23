<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use InvalidArgumentException;
use Woda\WordPress\TwoStageFontsLoader\Font;

final class FontTest extends AbstractTestCase
{
    private const PATH_BASE = 'https://www.woda.at/fonts/';

    /** @var Font */
    private $font;

    public function setUp(): void
    {
        $this->font = new Font(
            'Open Sans',
            [
                self::PATH_BASE . 'open-sans.ttf',
                self::PATH_BASE . 'open-sans.woff',
                self::PATH_BASE . 'open-sans.woff2',
            ],
            '700',
            true
        );
    }

    public function testFont(): void
    {

        self::assertSame('Open Sans', $this->font->name);
        self::assertSame(self::PATH_BASE . 'open-sans.ttf', $this->font->files[0]->url);
        self::assertSame(self::PATH_BASE . 'open-sans.woff', $this->font->files[1]->url);
        self::assertSame(self::PATH_BASE . 'open-sans.woff2', $this->font->files[2]->url);
        self::assertSame('700', $this->font->weight);
        self::assertSame(true, $this->font->italic);
    }

    public function testGetPrioritizedFile(): void
    {
        self::assertSame('https://www.woda.at/fonts/open-sans.woff2', $this->font->getPrioritizedFile()->url);
    }

    public function testFontThrowsExceptionWhenInvalidWeightPassed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Font weight "superbold" is not a valid value.');

        new Font(
            'Open Sans',
            [self::PATH_BASE . '/open-sans.woff2'],
            'superbold'
        );
    }
}
