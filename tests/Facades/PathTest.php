<?php

namespace Tests\Facades;

use Statamic\Facades\Path;
use Tests\TestCase;

class PathTest extends TestCase
{
    /** @test */
    public function makes_paths_relative()
    {
        $this->assertEquals('something', Path::makeRelative(base_path('something')));
    }
}
