<?php

namespace Woda\WordPress\TwoStageFontsLoader;

use Woda\WordPress\TwoStageFontsLoader\Core\Loader;

final class Init
{
    /**
     * @param array $settings
     */
    public static function init(array $settings = []): void
    {
        add_action('init', static function () use ($settings): void {
            Settings::init($settings);
        });

        add_action('wp_head', static function (): void {
            Loader::renderFontsLoaderBlock();
        }, 3);
    }
}
