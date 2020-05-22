<?php

namespace Woda\WordPress\TwoStageFontsLoader;

use Woda\WordPress\TwoStageFontsLoader\Utils\Error;

final class Settings
{
    public const FILTER = 'woda_two_stage_fonts_loader_settings';
    /**
     * @var array
     */
    private static $defaults;
    /**
     * @var array
     */
    public static $settings;

    /**
     * @param array $settings
     */
    public static function init(array $settings = []): void
    {
        self::$defaults = [
            'fontsDirUrl' => null,
            'stage1' => [],
            'stage2' => [],
            'classStage1' => 'fonts-loaded-stage1',
            'classStage2' => 'fonts-loaded-stage2',
            'preloadStage1' => false,
        ];
        $settings = array_merge(self::$defaults, $settings);
        self::$settings = apply_filters(self::FILTER, $settings);
        self::checkFontsDirUrl();
    }

    /**
     * @return string
     */
    public static function getClassStage1(): string
    {
        if (empty(self::$settings['classStage1'])) {
            return self::$defaults['classStage1'];
        }
        return self::$settings['classStage1'];
    }

    /**
     * @return string
     */
    public static function getClassStage2(): string
    {
        if (empty(self::$settings['classStage2'])) {
            return self::$defaults['classStage2'];
        }
        return self::$settings['classStage2'];
    }

    /**
     * @return string
     */
    public static function getFontsDirUrl(): string
    {
        return self::$settings['fontsDirUrl'] ?? '';
    }

    /**
     * @return array
     */
    public static function getStage1Fonts(): array
    {
        return self::$settings['stage1'] ?? [];
    }

    /**
     * @return array
     */
    public static function getStage2Fonts(): array
    {
        return self::$settings['stage2'] ?? [];
    }

    /**
     * @return bool
     */
    public static function shouldPreloadStage1(): bool
    {
        return self::$settings['preloadStage1'] ?? false;
    }

    private static function checkFontsDirUrl(): void
    {
        if (empty(self::getFontsDirUrl())) {
            Error::notice('"fontsDirUrl" must be set with the URL path to the directory containing the font files.');
        }
    }
}
