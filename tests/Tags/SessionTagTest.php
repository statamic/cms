<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Tests\TestCase;

class SessionTagTest extends TestCase
{
    #[Test]
    public function it_gets_session_value()
    {
        session()->put('nineties', 'rad');

        $this->assertEquals('rad', Antlers::parse('{{ session:value key="nineties" }}'));
    }

    #[Test]
    public function it_gets_session_array_value()
    {
        session()->put('things', ['nineties' => 'rad']);

        $this->assertEquals('rad', Antlers::parse('{{ session:value key="things.nineties" }}'));
        $this->assertEquals('rad', Antlers::parse('{{ session:value key="things:nineties" }}'));
    }

    #[Test]
    public function it_gets_session_value_using_wildcard()
    {
        session()->put('nineties', 'rad');

        $this->assertEquals('rad', Antlers::parse('{{ session:nineties }}'));
    }

    #[Test]
    public function it_gets_session_array_value_using_wildcard()
    {
        session()->put('things', ['nineties' => 'rad']);

        $this->assertEquals('rad', Antlers::parse('{{ session:things.nineties }}'));
        $this->assertEquals('rad', Antlers::parse('{{ session:things:nineties }}'));
    }
}
