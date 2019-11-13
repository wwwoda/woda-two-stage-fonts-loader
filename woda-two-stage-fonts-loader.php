<?php
/**
 * Plugin Name:     Woda Fonts Loader
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     woda-two-stage-fonts-loader
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Woda_Fonts_Loader
 */

include_once 'vendor/autoload.php';

add_action('init', static function (): void {
    $settings = apply_filters('woda_fonts_loader_settings', []);
    Woda\WordPress\FontsLoader\Loader::register($settings);
});
