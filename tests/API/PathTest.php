<?php

namespace Tests;

use Statamic\API\Path;

class PathTest extends TestCase
{
    /** @test */
    function makes_paths_relative()
    {
        $this->assertEquals('something', Path::makeRelative(base_path('something')));
    }
}