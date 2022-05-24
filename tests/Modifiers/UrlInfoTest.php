<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class UrlInfoTest extends TestCase
{
    /** @test */
    public function it_extracts_path_info_components()
    {
        $path = 'http://admin:password@example.com:8080/path?query=1#hash';

        $this->assertEquals('http', $this->modify($path, 'scheme'));
        $this->assertEquals('example.com', $this->modify($path, 'host'));
        $this->assertEquals('8080', $this->modify($path, 'port'));
        $this->assertEquals('admin', $this->modify($path, 'user'));
        $this->assertEquals('password', $this->modify($path, 'pass'));
        $this->assertEquals('/path', $this->modify($path, 'path'));
        $this->assertEquals('query=1', $this->modify($path, 'query'));
        $this->assertEquals('hash', $this->modify($path, 'fragment'));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->urlInfo($args)->fetch();
    }
}
