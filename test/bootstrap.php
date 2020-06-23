<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
WP_Mock::bootstrap();
WP_Mock::userFunction('plugin_dir_path', ['return_arg' => 0]);
WP_Mock::userFunction('plugin_dir_url', ['return_arg' => 0]);
