<?php

namespace Tests;

use Statamic\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The path to the bootstrap/app.php file
     *
     * @return string
     */
    protected function bootstrapAppFile()
    {
        return __DIR__ . '/../vendor/statamic/statamic/bootstrap/app.php';
    }
}
