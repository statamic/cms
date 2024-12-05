<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class PathinfoTest extends TestCase
{
    #[Test]
    public function it_extracts_pathinfo()
    {
        $path = '/directory/file.pdf';

        $this->assertEquals([
            'dirname' => '/directory',
            'basename' => 'file.pdf',
            'filename' => 'file',
            'extension' => 'pdf',
        ], $this->modify($path));
    }

    #[Test]
    public function it_extracts_pathinfo_components()
    {
        $path = '/directory/file.pdf';

        $this->assertEquals('/directory', $this->modify($path, 'dirname'));
        $this->assertEquals('file.pdf', $this->modify($path, 'basename'));
        $this->assertEquals('file', $this->modify($path, 'filename'));
        $this->assertEquals('pdf', $this->modify($path, 'extension'));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->pathinfo($args)->fetch();
    }
}
