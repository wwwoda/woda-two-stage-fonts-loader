<?php
/**
 * Plugin Name:     Woda Two Stage Fonts Loader
 * Plugin URI:      https://github.com/wwwoda/wp-plugin-two-stage-fonts-loader
 * Description:     This WordPress plugin provides a simple way for developers to implement a performant two stage font loading strategy.
 * Author:          Woda
 * Author URI:      https://www.woda.at
 * Text Domain:     woda-two-stage-fonts-loader
 * Domain Path:     /languages
 * Version:         0.1.2
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

namespace Woda\WordPress\TwoStageFontsLoader;

include_once 'vendor/autoload.php';

add_action('init', static function (): void {
    $settings = apply_filters('woda_two_stage_fonts_loader_settings', []);
    if (count($settings) < 1) {
        return;
    }
    Loader::register($settings);
});

$githubAccessToken = get_option('woda_admin_option_github_access_token');
if (!empty($githubAccessToken)) {
    $pluginUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
        'https://github.com/wwwoda/wp-plugin-two-stage-fonts-loader/',
        __FILE__,
        'woda-two-stage-fonts-loader'
    );
    $pluginUpdateChecker->setAuthentication($githubAccessToken);
}

add_action('admin_notices', static function (): void {
    $settings = apply_filters('woda_two_stage_fonts_loader_settings', []);
    if (count($settings) > 0) {
        return;
    }
    printf(
        '<div class="notice error"><p>%s <a target="_blank" href="%s">%s</a></p></div>',
        'Two Stage Fonts Loader is not active as long as you haven\'t registered settings.',
        'https://github.com/wwwoda/wp-plugin-two-stage-fonts-loader/wiki',
        'Look here for usage information.'
    );
});
