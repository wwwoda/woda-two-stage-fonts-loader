<?php

declare(strict_types=1);

namespace Woda\Test\WordPress\TwoStageFontsLoader\Unit;

use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

abstract class AbstractTestCase extends TestCase
{
    public function tearDown(): void
    {
        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
        WP_Mock::tearDown();
    }
}
