<?php

namespace Statamic\Testing;

use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;

abstract class TestCase extends IlluminateTestCase
{
    use CreatesApplication;

    /**
     * The path to the bootstrap/app.php file
     *
     * @return string
     */
    abstract protected function bootstrapAppFile();
}
