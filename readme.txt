=== Woda Two Stage Fonts Loader ===
Contributors: @davidmondok
Tags: fonts, performance
Requires at least: 4.5
Tested up to: 5.3
Stable tag: 0.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This WordPress plugin provides a simple way for developers to implement a performant two stage font loading strategy.

== Description ==

This is the long description.  No limit, and you can use Markdown (as well as in the following sections).

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

== Installation ==

1. Activate the plugin through the 'Plugins' menu in WordPress
2. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Changelog ==

= 0.0.1 =
* Provide basic features to implement font loading strategy

= 0.1.2 =
* Trigger error only if Query Monitor Plugin is activated

= 0.2.0 =
* Update GitHub updater to 4.9 to use Authorization HTTP header instead as using the `access_token` query parameter is deprecated and will be removed July 1st, 2020.
* Use constant GITHUB_ACCESS_TOKEN for updater if available
* Change fallback option key to woda_github_access_token

= 0.3.0 =
* Remove internal GitHub Updater logic

= 0.3.1 =
* Fix GitHub Plugin URI

`<?php code(); // goes in backticks ?>`
