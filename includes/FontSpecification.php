<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

final class FontSpecification
{
    /** @var Font */
    private $font;

    public function __construct(Font $font)
    {
        $this->font = $font;
    }

    public function getFontSpecificationString(): string
    {
        $fontSpecification = "'";

        if ($this->font->italic === true) {
            $fontSpecification .= 'italic ';
        }
        if (!in_array($this->font->weight, ['400', 'normal'], true)) {
            $fontSpecification .= $this->font->weight . ' ';
        }

        return $fontSpecification . '1em ' . $this->font->name . "'";
    }
}
