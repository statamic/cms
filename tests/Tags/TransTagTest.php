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

    #[Test]
    public function it_falls_back_to_fallback_for_missing_key()
    {
        $this->assertEquals('Fallback', $this->parse('{{ trans key="package::messages.does_not_exist" fallback="Fallback" }}'));
        $this->assertEquals('Bonjour', $this->parse('{{ trans key="package::messages.does_not_exist" fallback="package::messages.hello" locale="fr" }}'));
    }

    #[Test]
    public function it_applies_replacement_to_fallbacks()
    {
        $this->assertEquals('Fallback Bob', $this->parse('{{ trans key="package::messages.does_not_exist" name="Bob" fallback="Fallback :name" }}'));
        $this->assertEquals('Bonjour, Bob', $this->parse('{{ trans key="package::messages.does_not_exist" name="Bob" fallback="package::messages.hello_name" locale="fr" }}'));
    }
}
