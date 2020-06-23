<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

final class Loader
{
    /** @var bool */
    private $isPreloadersDisabled = false;
    /** @var Renderer */
    private $renderer;
    /** @var string */
    private $stage1Class = 'fonts-loaded-stage1';
    /** @var string */
    private $stage2Class = 'fonts-loaded-stage2';
    /** @var Font[] */
    private $stage1Fonts = [];
    /** @var Font[] */
    private $stage2Fonts = [];

    public function __construct()
    {
        $this->renderer = new Renderer($this);
    }

    public function addStage1Font(Font $font): Loader
    {
        $this->stage1Fonts[] = $font;
        return $this;
    }

    public function addStage2Font(Font $font): Loader
    {
        $this->stage2Fonts[] = $font;
        return $this;
    }

    public function disablePreloaders(): Loader
    {
        $this->isPreloadersDisabled = true;
        return $this;
    }

    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    public function getStage1Class(): string
    {
        return apply_filters(
            Config::loadConfigString('filter_prefix') . 'stage_1_class',
            $this->stage1Class
        );
    }

    public function getStage2Class(): string
    {
        return apply_filters(
            Config::loadConfigString('filter_prefix') . 'stage_2_class',
            $this->stage2Class
        );
    }

    /**
     * @return Font[]
     */
    public function getStage1Fonts(): array
    {
        return apply_filters(
            Config::loadConfigString('filter_prefix') . 'stage_1_fonts',
            $this->stage1Fonts
        );
    }

    /**
     * @return Font[]
     */
    public function getStage2Fonts(): array
    {
        return apply_filters(
            Config::loadConfigString('filter_prefix') . 'stage_2_fonts',
            $this->stage2Fonts
        );
    }

    public function isPreloadersDisabled(): bool
    {
        return apply_filters(
            Config::loadConfigString('filter_prefix') . 'preloader_disabled',
            $this->isPreloadersDisabled
        );
    }

    public function register(int $priority = 5): void
    {
        add_action('wp_head', [$this->renderer, 'render'], $priority);
    }

    public function setStage1Class(string $class): Loader
    {
        $this->stage1Class = $class;
        return $this;
    }

    public function setStage2Class(string $class): Loader
    {
        $this->stage2Class = $class;
        return $this;
    }
}
