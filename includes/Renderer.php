<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

final class Renderer
{
    /** @var Loader */
    private $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function render(): void
    {
        $this->renderPreloaders();
        $this->renderStyleBlock();
        $this->renderScriptBlock();
    }

    private function getFontFaces(): string
    {
        $fontFaces = [];
        foreach (array_merge($this->loader->getStage1Fonts(), $this->loader->getStage2Fonts()) as $font) {
            $fontFace = (new FontFace($font))->getFontFaceString();
            if (empty($fontFace)) {
                continue;
            }

            $fontFaces[] = $fontFace;
        }
        return implode('', $fontFaces);
    }

    /**
     * @param Font[] $fonts
     */
    private function getFontSpecifications(array $fonts = []): string
    {
        $specifications = [];

        foreach ($fonts as $font) {
            $specifications[] = (new FontSpecification($font))->getFontSpecificationString();
        }
        return implode(', ', $specifications);
    }

    private function getPreloaders(): string
    {
        $preloaders = [];
        foreach ($this->loader->getStage1Fonts() as $font) {
            $preloader = (new Preloader($font))->getPreloaderString();
            if (empty($preloader)) {
                continue;
            }

            $preloaders[] = $preloader;
        }
        return implode('', $preloaders);
    }

    private function renderPreloaders(): void
    {
        if ($this->loader->isPreloadersDisabled() === true) {
            return;
        }

        echo $this->getPreloaders();
    }

    private function renderScriptBlock(): void
    {
        /*
         * Minified code
         */
        echo "<script type=\"text/javascript\">var fontsInStorage=sessionStorage.foutFontsStage1Loaded&&" .
            "sessionStorage.foutFontsStage2Loaded;if(!fontsInStorage&&'fonts'in document){function fetchFonts(t){" .
            "return Promise.all(t.map(function(t){return document.fonts.load(t)}))}fetchFonts(["
            . $this->getFontSpecifications($this->loader->getStage1Fonts()) .
            "]).then(function(t){var e=document.documentElement;e.classList.add('" . $this->loader->getStage1Class() .
            "'),sessionStorage.foutFontsStage1Loaded=!0,fetchFonts(["
            . $this->getFontSpecifications($this->loader->getStage2Fonts()) .
            "]).then(function(t){e.classList.add('" . $this->loader->getStage2Class() .
            "'),sessionStorage.foutFontsStage2Loaded=!0})})}else{var docEl=document.documentElement;" .
            "docEl.classList.add('" . $this->loader->getStage1Class() . "'),docEl.classList.add('"
            . $this->loader->getStage2Class() . "')}</script>";
        /*
         * Unminified code
         */
//        echo "<script type=\"text/javascript\">
//         var fontsInStorage = sessionStorage.foutFontsStage1Loaded && sessionStorage.foutFontsStage2Loaded;
//         if (!fontsInStorage && 'fonts' in document) {
//           function fetchFonts(fonts) {
//             return Promise.all(fonts.map(function(font) {
//               return document.fonts.load(font);
//             }));
//           }
//           fetchFonts([" . $this->getFontSpecifications($this->loader->getStage1Fonts()) . "]).then(function(data) {
//             var docEl = document.documentElement;
//             docEl.classList.add('" . $this->loader->getStage1Class() . "');
//             sessionStorage.foutFontsStage1Loaded = true;
//             fetchFonts([" . $this->getFontSpecifications($this->loader->getStage2Fonts()) . "]).then(function(data) {
//               docEl.classList.add('" . $this->loader->getStage2Class() . "');
//               sessionStorage.foutFontsStage2Loaded = true;
//             });
//           });
//         } else {
//           var docEl = document.documentElement;
//           docEl.classList.add('" . $this->loader->getStage1Class() . "');
//           docEl.classList.add('" . $this->loader->getStage2Class() . "');
//         }
//        </script>";
    }

    private function renderStyleBlock(): void
    {
        printf("<style>%s</style>\n", $this->getFontFaces());
    }
}
