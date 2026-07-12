<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ParseUrlTest extends TestCase
{
    #[Test]
    public function it_extracts_parseurl()
    {
        $path = 'http://admin:password@example.com:8080/path?query=1#hash';

        $this->assertEquals([
            'scheme' => 'http',
            'host' => 'example.com',
            'port' => '8080',
            'user' => 'admin',
            'pass' => 'password',
            'path' => '/path',
            'query' => 'query=1',
            'fragment' => 'hash',
        ], $this->modify($path));
    }

    #[Test]
    public function it_extracts_parseurl_components()
    {
        $url = 'http://admin:password@example.com:8080/path?query=1#hash';

        $this->assertEquals('http', $this->modify($url, 'scheme'));
        $this->assertEquals('example.com', $this->modify($url, 'host'));
        $this->assertEquals('8080', $this->modify($url, 'port'));
        $this->assertEquals('admin', $this->modify($url, 'user'));
        $this->assertEquals('password', $this->modify($url, 'pass'));
        $this->assertEquals('/path', $this->modify($url, 'path'));
        $this->assertEquals('query=1', $this->modify($url, 'query'));
        $this->assertEquals('hash', $this->modify($url, 'fragment'));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->parse_url($args)->fetch();
    }
}
