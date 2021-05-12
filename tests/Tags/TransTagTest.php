<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Tests\TestCase;

class TransTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->copyLangFile('en/messages.php');
        $this->copyLangFile('fr/messages.php');
    }

    private function parse($tag)
    {
        return (string) Parse::template($tag, []);
    }

    /** @test */
    public function it_translates_message()
    {
        $this->assertEquals('Hello', $this->parse('{{ trans key="messages.hello" }}'));
    }

    /** @test */
    public function it_translates_with_replacement()
    {
        $this->assertEquals('Hello, Bob', $this->parse('{{ trans key="messages.hello_name" name="Bob" }}'));
    }

    /** @test */
    public function it_translates_to_specific_locale()
    {
        $this->assertEquals('Bonjour, Bob', $this->parse('{{ trans key="messages.hello_name" name="Bob" locale="fr" }}'));
        $this->assertEquals('Bonjour, Bob', $this->parse('{{ trans key="messages.hello_name" name="Bob" site="fr" }}'));
    }

    private function copyLangFile($path)
    {
        $files = app('files');

        $folder = preg_replace('/(.*)\/[^\/]*/', '$1', $path);

        if (! $files->exists($folderPath = resource_path("lang/$folder"))) {
            $files->makeDirectory($folderPath, 0755, true);
        }

        $files->copy(__DIR__."/../__fixtures__/lang/{$path}", resource_path("lang/{$path}"));
    }
}
