<?php

namespace Tests\Facades;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class ParseTest extends TestCase
{
    #[Test]
    public function it_parses_front_matter()
    {
        $this->assertEquals([
            'data' => ['foo' => 'bar'],
            'content' => 'test',
        ], Parse::frontMatter("---\nfoo: bar\n---\ntest"));
    }

    #[Test]
    public function it_parses_front_matter_with_crlf()
    {
        $this->assertEquals([
            'data' => ['foo' => 'bar'],
            'content' => 'test',
        ], Parse::frontMatter("---\r\nfoo: bar\r\n---\r\ntest"));
    }

    #[Test]
    public function it_parses_front_matter_when_theres_no_fence()
    {
        $this->assertEquals([
            'data' => [],
            'content' => 'test',
        ], Parse::frontMatter('test'));
    }

    #[Test]
    #[DataProvider('configPlaceholdersDataProvider')]
    public function it_parses_config_placeholders_and_passes_through_non_strings($input, $expected)
    {
        config(['app.name' => 'Test']);

        $this->assertSame($expected, Parse::config($input));
    }

    public static function configPlaceholdersDataProvider(): array
    {
        return [
            'colon notation' => ['{{ config:app:name }}', 'Test'],
            'mixed notation' => ['{{ config:app.name }}', 'Test'],
            'dot notation' => ['{{ config.app.name }}', 'Test'],
            'whitespace' => ['{{  config:app:name  }}', 'Test'],
            'multiple placeholders' => [
                '[name:{{ config:app.name }}] [missing:{{ config:missing:key }}]',
                '[name:Test] [missing:]',
            ],
            'non-config antlers' => ['{{ config:app:name }} {{ foo }}', 'Test {{ foo }}'],
            'modifiers passthrough' => ['{{ config:app:name | upper }}', '{{ config:app:name | upper }}'],
            'modifiers passthrough 2' => ['{{ config:app:name|upper }}', '{{ config:app:name|upper }}'],
            'array passthrough' => [
                ['foo' => 'bar'],
                ['foo' => 'bar'],
            ],
            'true passthrough' => [true, true],
            'null passthrough' => [null, null],
        ];
    }

    #[Test]
    public function app_key_is_banned_from_config_parsing()
    {
        config([
            'app.key' => 'secret',
            'foo.bar' => 'baz',
        ]);

        $this->assertSame('', Parse::config('{{ config:app:key }}'));
        $this->assertSame('', Parse::config('{{ config.app.key }}'));
        $this->assertSame('', Parse::config('{{ config.app:key }}'));
        $this->assertSame('prefix  baz suffix', Parse::config('prefix {{ config:app:key }} {{ config:foo:bar }} suffix'));
    }
}
