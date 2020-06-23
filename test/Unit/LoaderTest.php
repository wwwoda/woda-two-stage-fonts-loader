<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use Woda\WordPress\TwoStageFontsLoader\Font;
use Woda\WordPress\TwoStageFontsLoader\Loader;

final class LoaderTest extends AbstractTestCase
{
    private const PATH_BASE = 'https://www.woda.at/fonts';

    /** @var Loader */
    private $loader;
    /** @var Font */
    private $fontStage1;
    /** @var Font */
    private $fontStage2;

    public function setUp(): void
    {
        $this->fontStage1 = new Font(
            'Open Sans Initial',
            [
                self::PATH_BASE . '/open-sans.woff2',
                self::PATH_BASE . '/open-sans.woff',
            ]
        );
        $this->fontStage2 = new Font(
            'Open Sans',
            [
                self::PATH_BASE . '/open-sans.woff2',
                self::PATH_BASE . '/open-sans.woff',
            ]
        );
        $this->loader = (new Loader())
            ->addStage1Font($this->fontStage1)
            ->addStage2Font($this->fontStage2);
    }

    public function testAddStage1Font(): void
    {
        self::assertSame('Open Sans Initial', $this->loader->getStage1Fonts()[0]->name);
    }

    public function testAddStage2Font(): void
    {
        self::assertSame('Open Sans', $this->loader->getStage2Fonts()[0]->name);
    }

    public function testDisablePreloaders(): void
    {
        $this->loader->disablePreloaders();

        self::assertSame(true, $this->loader->isPreloadersDisabled());
    }

    public function testGetStage1Class(): void
    {
        \WP_Mock::onFilter('woda_two_stage_fonts_loader_stage_1_class')
            ->with('fonts-loaded-stage1')
            ->reply('fout-1-ok');

        self::assertSame('fout-1-ok', $this->loader->getStage1Class());
    }

    public function testGetStage2Class(): void
    {
        \WP_Mock::onFilter('woda_two_stage_fonts_loader_stage_2_class')
            ->with('fonts-loaded-stage2')
            ->reply('fout-2-ok');

        self::assertSame('fout-2-ok', $this->loader->getStage2Class());
    }

    public function testGetStage1Fonts(): void
    {
        \WP_Mock::onFilter('woda_two_stage_fonts_loader_stage_1_fonts')
            ->with([$this->fontStage1])
            ->reply([$this->fontStage2]);

        self::assertSame('Open Sans', $this->loader->getStage1Fonts()[0]->name);
    }

    public function testGetStage2Fonts(): void
    {
        \WP_Mock::onFilter('woda_two_stage_fonts_loader_stage_2_fonts')
            ->with([$this->fontStage2])
            ->reply([$this->fontStage1]);

        self::assertSame('Open Sans Initial', $this->loader->getStage2Fonts()[0]->name);
    }

    public function testIsPreloadersDisabled(): void
    {
        \WP_Mock::onFilter('woda_two_stage_fonts_loader_preloader_disabled')
            ->with(false)
            ->reply(true);

        self::assertSame(true, $this->loader->isPreloadersDisabled());
    }

    public function testRegister(): void
    {
        \WP_Mock::expectActionAdded('wp_head', [$this->loader->getRenderer(), 'render'], 1);

        $this->loader->register(1);
    }

    public function testSetStage1Class(): void
    {
        $this->loader->setStage1Class('fout-1-ok');

        self::assertSame('fout-1-ok', $this->loader->getStage1Class());
    }

    public function testSetStage2Class(): void
    {
        $this->loader->setStage2Class('fout-2-ok');

        self::assertSame('fout-2-ok', $this->loader->getStage2Class());
    }
}
