<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Tests\TestCase;

class CookieTagTest extends TestCase
{
    #[Test]
    public function it_gets_cookie_value()
    {
        request()->cookies->set('nineties', 'rad');

        $this->assertEquals('rad', Antlers::parse('{{ cookie:value key="nineties" }}'));
    }

    #[Test]
    public function it_gets_default_cookie_value()
    {
        $this->assertEquals('1', Antlers::parse('{{ cookie:value key="nineties" default="1" }}'));
    }

    #[Test]
    public function it_gets_cookie_value_using_wildcard()
    {
        request()->cookies->set('nineties', 'rad');

        $this->assertEquals('rad', Antlers::parse('{{ cookie:nineties }}'));
        $this->assertEquals('rad', Antlers::parse('{{ cookie:key }}', ['key' => 'nineties']));
    }
}
