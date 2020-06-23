<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit\Helper;

use Mockery\ExpectationInterface;
use WP_Mock;

final class WpMockHelper
{
    /**
     * @param array<int, mixed> $args
     * @param mixed $return
     */
    public static function expectCall(
        string $function,
        ?array $args = [],
        $return = null,
        int $times = 1
    ): ExpectationInterface {
        return WP_Mock::userFunction($function, ['args' => $args ?? [], 'times' => $times, 'return' => $return]);
    }
}
