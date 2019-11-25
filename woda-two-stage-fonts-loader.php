<?php
/**
 * Plugin Name:     Woda Two Stage Fonts Loader
 * Plugin URI:      https://github.com/wwwoda/wp-plugin-two-stage-fonts-loader
 * Description:     This WordPress plugin provides a simple way for developers to implement a performant two stage font loading strategy.
 * Author:          Woda
 * Author URI:      https://www.woda.at
 * Text Domain:     woda-two-stage-fonts-loader
 * Domain Path:     /languages
 * Version:         0.0.1
 *
 * @package         Woda_Two_Stage-Fonts_Loader
 */

// Copyright (c) 2019 Woda Digital OG. All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

include_once 'vendor/autoload.php';

use Puc_v4_Factory;

add_action('init', static function (): void {
    $settings = apply_filters('woda_two_stage_fonts_loader_settings', []);
    Woda\WordPress\TwoStageFontsLoader\Loader::register($settings);
});

$pluginUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/wwwoda/wp-plugin-two-stage-fonts-loader/',
    __FILE__,
    'woda/wp-two-stage-font-loader'
);

$pluginUpdateChecker->setAuthentication('f08473ebba7ba75e904082d63b328367d5d227a3');
