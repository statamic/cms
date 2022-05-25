<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class PathInfoTest extends TestCase
{
    /** @test */
    public function it_extracts_path_info_components()
    {
        $path = '/directory/file.pdf';

        $this->assertEquals('/directory', $this->modify($path, 'dirname'));
        $this->assertEquals('file.pdf', $this->modify($path, 'basename'));
        $this->assertEquals('file', $this->modify($path, 'filename'));
        $this->assertEquals('pdf', $this->modify($path, 'extname'));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->pathinfo($args)->fetch();
    }
}
