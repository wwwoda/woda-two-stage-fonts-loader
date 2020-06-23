<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

final class Preloader
{
    /** @var Font */
    private $font;

    public function __construct(Font $font)
    {
        $this->font = $font;
    }

    public function getPreloaderString(): string
    {
        $file = $this->font->getPrioritizedFile();
        return '<link rel="preload" href="' . $file->url . '" as="font" type="font/' .
            $file->extension . '" crossorigin>';
    }
}
