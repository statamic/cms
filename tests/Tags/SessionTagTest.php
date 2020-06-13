<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Tests\TestCase;

class SessionTagTest extends TestCase
{
    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    /** @test */
    public function it_gets_session_key()
    {
        session()->put('the-90s-are', 'rad');
        $this->assertEquals('rad', $this->tag('{{ session:value key="the-90s-are" }}'));
    }
}
