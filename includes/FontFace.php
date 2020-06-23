<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

final class FontFace
{
    /** @var Font */
    private $font;

    public function __construct(Font $font)
    {
        $this->font = $font;
    }

    public function getFontFaceString(): string
    {
        $fontSources = $this->getFontSources();
        if (empty($fontSources)) {
            return '';
        }
        return sprintf(
            '@font-face {font-family:"%s";src:%s;%s%s}',
            $this->font->name,
            $this->getFontSources(),
            $this->getWeight(),
            $this->getStyle()
        );
    }

    private function getWeight(): string
    {
        if (!in_array($this->font->weight, ['400', 'normal'], true)) {
            return 'font-weight:' . $this->font->weight . ';';
        }
        return '';
    }

    private function getStyle(): string
    {
        if ($this->font->italic === true) {
            return 'font-style:italic;';
        }
        return '';
    }

    private function getFontSources(): string
    {
        $fontSources = [];
        foreach ($this->font->files as $file) {
            $fontSources[] = $this->getFontSourceUrl($file);
        }
        return implode(',', $fontSources);
    }

    private function getFontSourceUrl(FontFile $file): string
    {
        return 'url("' . $file->url . '") format("' . $file->extension . '")';
    }
}
