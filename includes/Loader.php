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
     * Function to be executed on wp_head action hook
     */
    public static function renderFontsLoaderBlock(): void
    {
        self::renderPreloadLinksBlock();
        self::renderStyleBlock();
        self::renderScriptBlock();
    }

    /**
     * Renders all <link> elements for preloading the stage one fonts
     */
    private static function renderPreloadLinksBlock(): void
    {
        echo self::getPreloadLinkTags(self::getStageOneFontConfigs());
    }

    /**
     * Renders the <style> block containing all @font-face rules
     */
    private static function renderStyleBlock(): void
    {
        printf("<style>%s</style>\n", self::getFontFaceCssCode(self::getMergedFontConfigs()));
    }

    /**
     * Renders the <script> block containing the font face observer vendor script and the two stage font loader logic
     */
    private static function renderScriptBlock(): void
    {
        printf(
            '<script type="text/javascript">' .
            // Setup a self-invoking function and inject the font face observer vendor code first
            '(function(){%1$s' .
            // If the browser doesn't support Promises or the fonts have already been loaded in the current session
            // add both loaded classes to <html>
            'if(!window.Promise||sessionStorage.%6$s===true){document.documentElement.className+=\' %7$s %8$s\'}' .
            // Else setup the stage one font face observers
            'else{%2$s' .
            // Load the stage one fonts
            'Promise.all([%3$s]).then(function(){' .
            // Add the stage one loaded class to <html>
            'document.documentElement.className+=\' %7$s\';' .
            // Setup the stage two font face observers
            '%4$s' .
            // Load the stage two fonts
            'Promise.all([%5$s]).then(function(){' .
            // Add the stage two loaded class to <html> and udpate the session storage as fonts can be loaded from the
            // cache on future page loads in the current sesssion.
            'document.documentElement.className+=\' %8$s\';sessionStorage.%6$s=true})' .
            // In case of error while loading the stage two fonts add both loaded classes to <html>
            '.catch(function(){sessionStorage.%6$s=false;document.documentElement.className+=\' %7$s %8$s\'})' .
            '})' .
            // In case of error while loading the stage one fonts add both loaded classes to <html>
            '.catch(function(){sessionStorage.%6$s=false;document.documentElement.className+=\' %7$s %8$s\'})' .
            '}})();</script>',
            self::getFontFaceObserverVendorScript(),
            self::getFontFaceObserverJavaScriptCode(self::getStageOneFontConfigs()),
            self::getFontFaceChecksJavaScriptCode(self::getStageOneFontConfigs()),
            self::getFontFaceObserverJavaScriptCode(self::getStageTwoFontConfigs(), 2),
            self::getFontFaceChecksJavaScriptCode(self::getStageTwoFontConfigs(), 2),
            self::getSessionStorageVariable(),
            self::getStageClasses()[0],
            self::getStageClasses()[1]
        );
    }

    /**
     * Create a list of <link> elements for preloading the stage one fonts
     *
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
     * Prioritize the most modern font format
     *
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
     * Create @font-face css rules for a collection of fonts
     *
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
     * Combine a url() and format() function to create the value for a @font-face src: descriptor
     *
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
     * Returns the js code for the font face observer vendor script
     *
     * @return string
     */
    private static function getFontFaceObserverVendorScript(): string
    {
        $fontsFaceObserverScriptPath = dirname(__DIR__) . '/assets/fontfaceobserver.standalone.js';
        return file_get_contents($fontsFaceObserverScriptPath);
    }

    /**
     * Create a list of font face observers for a specific stage
     *
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
     * Create a list of font face loaders for a specific stage
     *
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
    private static function getStageOneFontConfigs(): array
    {
        return self::$settings['stage1'] ?? [];
    }

    /**
     * @return array
     */
    private static function getStageTwoFontConfigs(): array
    {
        return self::$settings['stage2'] ?? [];
    }

    /**
     * @return array
     */
    private static function getMergedFontConfigs(): array
    {
        return array_merge(self::getStageOneFontConfigs(), self::getStageTwoFontConfigs());
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
     * Checks the main settings passed to the plugin and gives feedback when incorrectly set up
     *
     * @return bool
     */
    private static function checkSettings(): bool
    {
        $check = true;

        if (count(self::getStageOneFontConfigs()) < 1) {
            self::triggerError('No font settings found for stage 1');
            $check = false;
        }

        if (count(self::getStageTwoFontConfigs()) < 1) {
            self::triggerError('No font settings found for stage 2');
            $check = false;
        }

        foreach (self::getStageOneFontConfigs() as $font) {
            if (self::checkFontSetting($font) === false) {
                $check = false;
            }
        }

        foreach (self::getStageTwoFontConfigs() as $font) {
            if (self::checkFontSetting($font) === false) {
                $check = false;
            }
        }

        return $check;
    }

    /**
     * Checks the font settings passed to the plugin and gives feedback when incorrectly set up
     *
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
            self::triggerError('Font name missing');
            $check = false;
        }

        if (! array_key_exists('filename', $font) || empty($font['filename'])) {
            self::triggerError('Font file name missing name');
            $check = false;
        }

        if (! array_key_exists('extensions', $font)) {
            self::triggerError('Font extension missing');
            $check = false;
        } elseif (! is_array($font['extensions'])) {
            self::triggerError('Font file extensions need to be an array');
            $check = false;
        } elseif (count(array_intersect($font['extensions'], $allowedFontFamilies)) < 1) {
            self::triggerError('Font file extensions array needs to contain either woff, woff2, ttf or any combination of these');
            $check = false;
        } elseif (count(array_diff($font['extensions'], $allowedFontFamilies)) < 0) {
            self::triggerError('Font file extensions array contains unkonwn extensions (allowed: ttf, woff, woff2)');
            $check = false;
        }

        if (! array_key_exists('italic', $font)) {
            self::triggerError('Font italic setting missing');
            $check = false;
        } elseif (! is_bool($font['italic'])) {
            self::triggerError('Font italic setting is not a boolean');
            $check = false;
        }

        if (! array_key_exists('weight', $font) || empty($font['weight'])) {
            self::triggerError('Font weight setting missing');
            $check = false;
        } elseif (! in_array($font['weight'], $allowedFontWeights, true)) {
            self::triggerError($font['weight'] . ' is an illegal font weight');
            $check = false;
        }

        return $check;
    }

    /**
     * Trigger errors only if Query Monitor is activated
     *
     * @param $msg
     * @param int $errorType
     */
    private static function triggerError($msg, $errorType = E_USER_NOTICE): void
    {
        if (class_exists('QM_Activation') === false) {
            return;
        }

        trigger_error($msg, $errorType);
    }
}
