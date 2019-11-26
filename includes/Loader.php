<?php

namespace Woda\WordPress\TwoStageFontsLoader;

final class Loader
{
    /** @var array */
    public static $settings;
    /** @var string  */
    public static $variableBase = 'font';
    /** @var array  */
    public static $stageClasses = [
        'fonts-1-loaded',
        'fonts-2-loaded',
    ];
    /** @var string  */
    public static $sessionsStorageVariable = 'fontsLoaded';


    /**
     * @param array|null $settings
     */
    public static function register(?array $settings = []): void
    {
        self::$settings = $settings;
        if (self::checkSettings() === true) {
            add_action('wp_head', [self::class, 'renderFontsLoaderBlock'], 3);
        }
    }

    /**
     *
     */
    public static function renderFontsLoaderBlock(): void
    {
        self::renderPreloadLinksBlock();
        self::renderStyleBlock();
        self::renderScriptBlock();
    }

    /**
     *
     */
    private static function renderPreloadLinksBlock(): void
    {
        echo self::getPreloadLinkTags(self::getStageOneFonts());
    }

    /**
     *
     */
    private static function renderStyleBlock(): void
    {
        printf("<style>%s</style>\n", self::getFontFaceCssCode(self::getBothStageFonts()));
    }

    /**
     *
     */
    private static function renderScriptBlock(): void
    {
        printf(
            self::getScriptTemplate(),
            self::getFontFaceObserverVendorScript(),
            self::getFontFaceObserverJavaScriptCode(self::getStageOneFonts()),
            self::getFontFaceChecksJavaScriptCode(self::getStageOneFonts()),
            self::getFontFaceObserverJavaScriptCode(self::getStageTwoFonts(), 2),
            self::getFontFaceChecksJavaScriptCode(self::getStageTwoFonts(), 2)
        );
    }

    /**
     * @param array $fonts
     * @return string
     */
    private static function getPreloadLinkTags(array $fonts = []): string
    {
        $preloadLinkTagsString = '';
        foreach ($fonts as $font) {
            $preloadLinkTagsString .= sprintf(
                '<link rel="preload" href="%s/%s.%s" as="font" type="font/%3$s" crossorigin>',
                self::getFontsUrl(),
                $font['filename'],
                self::getPriorityFontExtension($font['extensions'])
            );
            $preloadLinkTagsString .= "\n";
        }
        return $preloadLinkTagsString;
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
    private static function getFontFaceCssCode(array $fonts): string
    {
        $fontFacesString = '';
        foreach ($fonts as $font) {
            $fontFacesString .= sprintf(
                "@font-face {\n    font-family: '%s';\n    src: %s;\n    font-weight: %s;%s\n    font-display: swap;\n}\n",
                $font['name'],
                self::getFontSourceCssCode($font),
                $font['weight'],
                $font['italic'] === true ? "\n    font-style: italic;" : ''
            );
        }
        return $fontFacesString;
    }

    /**
     * @param array $font
     * @return string
     */
    private static function getFontSourceCssCode(array $font): string
    {
        $fontSrcString = '';
        $extensionsCount = count($font['extensions']);
        foreach ($font['extensions'] as $index => $ext) {
            $fontSrcString .= sprintf(
                'url("%1$s/%2$s.%3$s") format("%3$s")%4$s',
                self::getFontsUrl(),
                $font['filename'],
                $ext,
                $index < $extensionsCount - 1 ? ', ' : ''
            );
        }
        return $fontSrcString;
    }

    /**
     * @return string
     */
    private static function getScriptTemplate(): string
    {
        return '<script>(function () {%s if (!window.Promise || sessionStorage.' . self::getSessionStorageVariable() . ") {document.documentElement.className += ' " . self::getStageClasses()[0] . ' ' . self::getStageClasses()[1] . "';} else {%sPromise.all([%s]).then(function () { document.documentElement.className += ' " . self::getStageClasses()[0] . "';%sPromise.all([%s]).then(function(){document.documentElement.className+=' " . self::getStageClasses()[1] . "';sessionStorage." . self::getSessionStorageVariable() . '=true;}).catch(function(){sessionStorage.' . self::getSessionStorageVariable() . "=false;document.documentElement.className+=' fonts-1-loaded fonts-2-loaded';});}).catch(function(){sessionStorage." . self::getSessionStorageVariable() . "=false;document.documentElement.classNam+=' fonts-1-loaded fonts-2-loaded';});}})();</script>";
    }

    /**
     * @return string
     */
    private static function getFontFaceObserverVendorScript(): string
    {
        $fontsFaceObserverScriptPath = dirname(__DIR__) . '/assets/fontfaceobserver.standalone.js';
        return file_get_contents($fontsFaceObserverScriptPath);
    }

    /**
     * @param array $fonts
     * @param int $stage
     * @return string
     */
    private static function getFontFaceObserverJavaScriptCode(array $fonts, int $stage = 1): string
    {
        $observersString = '';
        foreach ($fonts as $index => $font) {
            $observersString .= sprintf(
                'var %s_%d=new window.FontFaceObserver("%s",{weight:"%s"%s});',
                self::$variableBase . $stage,
                $index + 1,
                $font['name'],
                $font['weight'],
                $font['italic'] === true ? ',style:"italic"' : ''
            );
        }
        return $observersString;
    }

    /**
     * @param array $fonts
     * @param int $stage
     * @param int|null $timeout
     * @return string
     */
    private static function getFontFaceChecksJavaScriptCode(array $fonts, int $stage = 1, int $timeout = null): string
    {
        $checks = '';
        $fontsCount = count($fonts);
        for ($i = 0; $i < $fontsCount; $i++) {
            $checks .= sprintf('%s_%d.load(%s),', self::$variableBase . $stage, $i + 1, $timeout > 0 ? 'null, ' . $timeout : '');
        }
        return rtrim($checks, ',');
    }

    /**
     * @return array
     */
    private static function getStageOneFonts(): array
    {
        return self::$settings['stage1'] ?? [];
    }

    /**
     * @return array
     */
    private static function getStageTwoFonts(): array
    {
        return self::$settings['stage2'] ?? [];
    }

    /**
     * @return array
     */
    private static function getBothStageFonts(): array
    {
        return array_merge(self::getStageOneFonts(), self::getStageTwoFonts());
    }

    /**
     * @return string
     */
    private static function getFontsUrl(): string
    {
        return self::$settings['fontsUrl'];
    }

    /**
     * @return array
     */
    private static function getStageClasses(): array
    {
        if (array_key_exists('stageClasses', self::$settings) && count(self::$settings['stageClasses']) === 2) {
            return [
                self::$settings['stageClasses'][1] ?: self::$stageClasses[1],
                self::$settings['stageClasses'][2] ?: self::$stageClasses[2],
            ];
        }
        return self::$stageClasses;
    }

    /**
     * @return string
     */
    private static function getSessionStorageVariable(): string
    {
        if (array_key_exists('sessionsStorageVariable', self::$settings) && ! empty(self::$settings['sessionsStorageVariable'])) {
            return self::$settings['sessionsStorageVariable'];
        }
        return self::$sessionsStorageVariable;
    }

    /**
     * @return bool
     */
    private static function checkSettings(): bool
    {
        $check = true;

        if (count(self::getStageOneFonts()) < 1) {
            trigger_error('No font settings found for stage 1', E_USER_NOTICE);
            $check = false;
        }

        if (count(self::getStageTwoFonts()) < 1) {
            trigger_error('No font settings found for stage 2', E_USER_NOTICE);
            $check = false;
        }

        foreach (self::getStageOneFonts() as $font) {
            if (self::checkFontSetting($font) === false) {
                $check = false;
            }
        }

        foreach (self::getStageTwoFonts() as $font) {
            if (self::checkFontSetting($font) === false) {
                $check = false;
            }
        }

        return $check;
    }

    /**
     * @param array $font
     * @return bool
     */
    private static function checkFontSetting(array $font): bool
    {
        $check = true;
        $allowedFontFamilies = [
            'ttf',
            'woff',
            'woff2'
        ];
        $allowedFontWeights = [
            'normal',
            'bold',
            'lighter',
            'bolder',
            '1',
            '100',
            '100.6',
            '123',
            '200',
            '300',
            '321',
            '400',
            '500',
            '600',
            '700',
            '800',
            '900',
            '1000'
        ];

        if (! array_key_exists('name', $font) || empty($font['name'])) {
            trigger_error('Font name missing', E_USER_NOTICE);
            $check = false;
        }

        if (! array_key_exists('filename', $font) || empty($font['filename'])) {
            trigger_error('Font file name missing name', E_USER_NOTICE);
            $check = false;
        }

        if (! array_key_exists('extensions', $font)) {
            trigger_error('Font extension missing', E_USER_NOTICE);
            $check = false;
        } elseif (! is_array($font['extensions'])) {
            trigger_error('Font file extensions need to be an array', E_USER_NOTICE);
            $check = false;
        } elseif (count(array_intersect($font['extensions'], $allowedFontFamilies)) < 1) {
            trigger_error('Font file extensions array needs to contain either woff, woff2, ttf or any combination of these', E_USER_NOTICE);
            $check = false;
        } elseif (count(array_diff($font['extensions'], $allowedFontFamilies)) < 0) {
            trigger_error('Font file extensions array contains unkonwn extensions (allowed: ttf, woff, woff2)', E_USER_NOTICE);
            $check = false;
        }

        if (! array_key_exists('italic', $font)) {
            trigger_error('Font italic setting missing', E_USER_NOTICE);
            $check = false;
        } elseif (! is_bool($font['italic'])) {
            trigger_error('Font italic setting is not a boolean', E_USER_NOTICE);
            $check = false;
        }

        if (! array_key_exists('weight', $font) || empty($font['weight'])) {
            trigger_error('Font weight setting missing', E_USER_NOTICE);
            $check = false;
        } elseif (! in_array($font['weight'], $allowedFontWeights, true)) {
            trigger_error($font['weight'] . ' is an illegal font weight', E_USER_NOTICE);
            $check = false;
        }

        return $check;
    }
}
