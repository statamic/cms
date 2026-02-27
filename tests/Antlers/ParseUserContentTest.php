<?php

namespace Tests\Antlers;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Facades\Antlers;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\Antlers\Fixtures\MethodClasses\ClassOne;
use Tests\TestCase;

class ParseUserContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::resetGlobalState();
        GlobalRuntimeState::$throwErrorOnAccessViolation = false;
        GlobalRuntimeState::$allowPhpInContent = false;
        GlobalRuntimeState::$allowMethodsInContent = false;
    }

    #[Test]
    public function it_parses_templates_like_standard_parse_for_basic_content()
    {
        $this->assertSame(
            (string) Antlers::parse('Hello {{ name }}!', ['name' => 'Jason']),
            (string) Antlers::parseUserContent('Hello {{ name }}!', ['name' => 'Jason'])
        );
    }

    #[Test]
    public function it_blocks_php_nodes_in_user_content_mode()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('PHP Node evaluated in user content: {{? echo Str::upper(\'hello\') ?}}', \Mockery::type('array'));

        $result = (string) Antlers::parseUserContent('Text: {{? echo Str::upper(\'hello\') ?}}');

        $this->assertSame('Text: ', $result);
    }

    #[Test]
    public function it_blocks_method_calls_in_user_content_mode()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Method call evaluated in user content.', \Mockery::type('array'));

        $result = (string) Antlers::parseUserContent('{{ object:method("hello") }}', [
            'object' => new ClassOne(),
        ]);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_restores_user_data_flag_after_successful_parse()
    {
        GlobalRuntimeState::$isEvaluatingUserData = false;

        Antlers::parseUserContent('Hello {{ name }}!', ['name' => 'Jason']);

        $this->assertFalse(GlobalRuntimeState::$isEvaluatingUserData);
    }

    #[Test]
    public function it_restores_user_data_flag_after_parse_exceptions()
    {
        GlobalRuntimeState::$isEvaluatingUserData = false;
        $parser = \Mockery::mock(Parser::class);
        $parser->shouldReceive('parse')
            ->once()
            ->andThrow(new \RuntimeException('Failed to parse user content.'));

        try {
            Antlers::usingParser($parser, function ($antlers) {
                $antlers->parseUserContent('Hello {{ name }}', ['name' => 'Jason']);
            });

            $this->fail('Expected RuntimeException to be thrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Failed to parse user content.', $exception->getMessage());
        }

        $this->assertFalse(GlobalRuntimeState::$isEvaluatingUserData);
    }
}
