<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Tests\TestCase;

class VariableTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function parse($tag)
    {
        return (string) Parse::template($tag, []);
    }

    /** @test */
    public function it_remembers_a_variable()
    {
        $this->parse('{{ variable:foo value="remember me" }}');
        $this->assertEquals('remember me', $this->parse('{{ variable:foo }}'));
    }

    /** @test */
    public function it_gets_default_value()
    {
        $this->parse('{{ variable:foo value="remember me" }}');
        $this->assertEquals('bar', $this->parse('{{ variable:bar default="bar" }}'));
    }

    /** @test */
    public function it_returns_nothing_if_not_found()
    {
        $this->assertEquals('', $this->parse('{{ variable:bar }}'));
    }
}
