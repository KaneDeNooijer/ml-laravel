<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Pages render without built assets or a running Vite server. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
