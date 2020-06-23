<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

use RuntimeException;

final class Config
{
    /** @var array<string, mixed> */
    public static $config = [];

    /**
     * @return array<string, mixed>
     */
    public static function loadConfigArray(string $key): array
    {
        self::checkConfig();
        $value = self::$config[$key] ?? null;
        if (!is_array($value)) {
            throw new RuntimeException(sprintf('Required config key "%s" not found.', $key));
        }
        return $value;
    }

    public static function loadConfigString(string $key): string
    {
        self::checkConfig();
        $value = self::$config[$key] ?? null;
        if (!is_string($value)) {
            throw new RuntimeException(sprintf('Required config key "%s" not found.', $key));
        }
        return $value;
    }

    private static function checkConfig(): void
    {
        if (!empty(self::$config)) {
            return;
        }

        self::$config = include __DIR__ . '/../config/plugin.config.php';
    }
}
