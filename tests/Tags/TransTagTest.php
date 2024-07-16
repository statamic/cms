<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class TransTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app('translator')->addNamespace('package', __DIR__.'/../__fixtures__/lang');
    }

    private function parse($tag)
    {
        return (string) Parse::template($tag, []);
    }

    #[Test]
    public function it_translates_message()
    {
        $this->assertEquals('Hello', $this->parse('{{ trans key="package::messages.hello" }}'));
    }

    #[Test]
    public function it_translates_with_replacement()
    {
        $this->assertEquals('Hello, Bob', $this->parse('{{ trans key="package::messages.hello_name" name="Bob" }}'));
    }

    #[Test]
    public function it_translates_to_specific_locale()
    {
        $this->assertEquals('Bonjour, Bob', $this->parse('{{ trans key="package::messages.hello_name" name="Bob" locale="fr" }}'));
        $this->assertEquals('Bonjour, Bob', $this->parse('{{ trans key="package::messages.hello_name" name="Bob" site="fr" }}'));
    }
}
