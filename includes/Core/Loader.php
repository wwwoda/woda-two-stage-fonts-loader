<?php

namespace Woda\WordPress\TwoStageFontsLoader\Core;

use Woda\WordPress\TwoStageFontsLoader\Font;
use Woda\WordPress\TwoStageFontsLoader\Settings;

final class Loader
{
    public static function renderFontsLoaderBlock(): void
    {
        self::renderPreloaders();
        self::renderStyleBlock();
        self::renderScriptBlock();
    }

    private static function renderPreloaders(): void
    {
        if (Settings::shouldPreloadStage1() === true) {
            echo self::getPreloaderCollection(Settings::getStage1Fonts());
        }
    }

    private static function renderStyleBlock(): void
    {
        printf("<style>%s</style>\n", self::getFontFaceCollection(self::getMergedFontConfigs()));
    }

    private static function renderScriptBlock(): void
    {
		/*
		 * Minified code
		 */
        echo "<script type=\"text/javascript\">var fontsInServiceWorker=sessionStorage.foutFontsStage1Loaded&&sessionStorage.foutFontsStage2Loaded||('serviceWorker'in navigator&&navigator.serviceWorker.controller!==null&&navigator.serviceWorker.controller.state==='activated');if(!fontsInServiceWorker&&'fonts'in document){function fetchFonts(fonts) {return Promise.all(fonts.map(function (font) {return document.fonts.load(font);}));}fetchFonts([".self::getFontSpecificationCollection(Settings::getStage1Fonts())."]).then(function(){var docEl=document.documentElement;docEl.classList.add('" . Settings::getClassStage1() . "');sessionStorage.foutFontsStage1Loaded=true;fetchFonts([" . self::getFontSpecificationCollection(Settings::getStage2Fonts()) . "]).then(function(){docEl.classList.add('" . Settings::getClassStage2() . "');sessionStorage.foutFontsStage2Loaded=true;});});}else{var docEl=document.documentElement;docEl.classList.add('" . Settings::getClassStage1() . "');docEl.classList.add('" . Settings::getClassStage2() . "');}</script>";
        /*
         * Unminified code
         */
        // echo "<script type=\"text/javascript\">
        //     var fontsInServiceWorker = sessionStorage.foutFontsStage1Loaded
        //       && sessionStorage.foutFontsStage2Loaded
        //       || ('serviceWorker' in navigator
        //       && navigator.serviceWorker.controller !== null
        //       && navigator.serviceWorker.controller.state === 'activated')
        //     if (!fontsInServiceWorker && 'fonts' in document) {
        //       function fetchFonts(fonts) {
        //         return Promise.all(fonts.map(function (font) {
        //           return document.fonts.load(font);
        //         }));
        //       }
        //       fetchFonts([" .self::getFontSpecificationCollection(Settings::getStage1Fonts()). "]).then(function (data) {
        //         var docEl = document.documentElement;
        //         docEl.classList.add('" . Settings::getClassStage1() . "');
        //         sessionStorage.foutFontsStage1Loaded = true;
        //         fetchFonts([" . self::getFontSpecificationCollection(Settings::getStage2Fonts()) . "]).then(function (data) {
        //           docEl.classList.add('" . Settings::getClassStage2() . "');
        //           sessionStorage.foutFontsStage2Loaded = true;
        //         });
        //       });
        //     } else {
        //       var docEl = document.documentElement;
        //       docEl.classList.add('" . Settings::getClassStage1() . "');
        //       docEl.classList.add('" . Settings::getClassStage2() . "');
        //     }
        // </script>";
    }

    /**
     * @param array $fonts
     * @return string
     */
    private static function getFontSpecificationCollection(array $fonts = []): string
    {
        $specifications = [];

        foreach ($fonts as $font) {
            $specifications[] = self::getFontSpecification($font);
        }
        return implode(', ', $specifications);
    }

    /**
	 * Returns a font specification using the CSS value syntax, e.g. "italic bold 16px Roboto"
	 *
     * @param Font $font
     * @return string
     */
    private static function getFontSpecification(Font $font): string
    {
        $fontSpecification = "'";

        if ($font->italic === true) {
            $fontSpecification .= 'italic ';
        }
        if (!in_array($font->weight, ['400', 'normal'], true)) {
            $fontSpecification .= $font->weight . ' ';
        }

        return $fontSpecification . '1em ' . $font->name . "'";
    }

    /**
     * @param array $fonts
     * @return string
     */
    private static function getPreloaderCollection(array $fonts = []): string {
        $preloaders = [];
        foreach ($fonts as $font) {
            $preloaders[] = self::getPreloader($font);
        }
        return implode('', $preloaders);
    }

    /**
     * @param Font $font
     * @return string
     */
    private static function getPreloader(Font $font): string
    {
        $extension = self::getPriorityFontExtension($font->extensions);
        return '<link rel="preload" href="' . Settings::getFontsDirUrl() . '/' . $font->filename . '.' . $extension .
            '" as="font" type="font/' . $extension . '" crossorigin>';
    }

    /**
     * @param array $extensions
     * @return string
     */
    private static function getPriorityFontExtension(array $extensions = []): string
    {
        if (in_array('woff2', $extensions, true)) {
            return 'woff2';
        }
        if (in_array('woff', $extensions, true)) {
            return 'woff';
        }
        return 'ttf';
    }

    /**
     * @param array $fonts
     * @return string
     */
    private static function getFontFaceCollection(array $fonts): string
    {
        $fontFaces = [];
        foreach ($fonts as $font) {
            $fontFaces[] = self::getFontFace($font);
        }
        return implode('', $fontFaces);
    }

    /**
     * @param Font $font
     * @return string
     */
    private static function getFontFace(Font $font): string
    {
        $fontFace = '@font-face {font-family:' . $font->name . ';src:' . self::getFontSource($font) . ';';
        if (!in_array($font->weight, ['400', 'normal'], true)) {
            $fontFace .= 'font-weight:' . $font->weight . ';';
        }
        if ($font->italic === true) {
            $fontFace .= 'font-style:italic;';
        }
        return $fontFace . '}';
    }

    /**
     * @param Font $font
     * @return string
     */
    private static function getFontSource(Font $font): string
    {
        $fontSources = [];
        foreach ($font->extensions as $index => $extension) {
            $fontSources[] = self::getFontSourceUrl($font->filename, $extension);
        }
        return implode(',', $fontSources);
    }

    /**
     * @param string $filename
     * @param string $extension
     * @return string
     */
    private static function getFontSourceUrl(string $filename, string $extension): string
    {
        return 'url("' . Settings::getFontsDirUrl() . '/' . $filename . '.' . $extension . '") format("' . $extension . '")';
    }

    /**
     * @return array
     */
    private static function getMergedFontConfigs(): array
    {
        return array_merge(Settings::getStage1Fonts(), Settings::getStage2Fonts());
    }
}
