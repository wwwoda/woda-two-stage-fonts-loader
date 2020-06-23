<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use InvalidArgumentException;
use Woda\WordPress\TwoStageFontsLoader\FontFile;

final class FontFileTest extends AbstractTestCase
{
    private const PATH_BASE = 'https://www.woda.at/fonts/';

    public function testFontFile(): void
    {
        $fontFile = new FontFile(self::PATH_BASE . 'font.woff2');

        self::assertSame('https://www.woda.at/fonts/font.woff2', $fontFile->url);
        self::assertSame('/fonts/font.woff2', $fontFile->path);
        self::assertSame('font.woff2', $fontFile->baseName);
        self::assertSame('woff2', $fontFile->extension);
    }

    public function testHasValidExtensionWhenPassingWOFF2(): void
    {
        self::assertSame(true, (new FontFile(self::PATH_BASE . 'font.woff2'))->hasValidExtension());
    }

    public function testHasValidExtensionWhenPassingWOFF(): void
    {
        self::assertSame(true, (new FontFile(self::PATH_BASE . 'font.woff'))->hasValidExtension());
    }

    public function testHasValidExtensionWhenPassingTTF(): void
    {
        self::assertSame(true, (new FontFile(self::PATH_BASE . 'font.ttf'))->hasValidExtension());
    }

    public function testHasValidExtensionThrowsExceptionWhenPassingOTF(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid extension "otf" for file "font.otf" (allowed: ttf, woff, woff2).');

        (new FontFile(self::PATH_BASE . 'font.otf'))->hasValidExtension();
    }

    public function testHasValidExtensionThrowsExceptionWhenPassingEOT(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid extension "eot" for file "font.eot" (allowed: ttf, woff, woff2).');

        (new FontFile(self::PATH_BASE . 'font.eot'))->hasValidExtension();
    }

    public function testHasValidExtensionThrowsExceptionWhenPassingSVG(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid extension "svg" for file "font.svg" (allowed: ttf, woff, woff2).');

        (new FontFile(self::PATH_BASE . 'font.svg'))->hasValidExtension();
    }
}
