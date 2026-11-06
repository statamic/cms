<?php

namespace Tests\View\Instrumentation;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\Antlers\ParserTestCase;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;

class PostProcessingTagInstrumentationTest extends ParserTestCase
{
    use DecodesMarkers, InteractsWithRuntimeConfigurations;

    #[DataProvider('templates')]
    public function test_tags_that_post_process_their_body_render_the_same_with_instrumentation(string $template, string $contains)
    {
        $data = ['title' => 'Hello there world'];
        $parser = $this->parser($data, true);
        $parser->setRuntimeConfiguration($this->runtimeConfiguration());

        $plain = (string) $parser->parse($template, $data);

        $this->assertStringContainsString($contains, $plain);

        Runtime::register(HtmlInstrumentation::make());

        $instrumented = (string) $parser->parse($template, $data);

        $this->assertNotSame($plain, $instrumented);
        $this->assertNotEmpty($this->markerExpressions($instrumented));
        $this->assertSame($plain, Runtime::removeMarkers($instrumented));
    }

    public static function templates(): array
    {
        return [
            'markdown' => [
                "<div>{{ markdown }}# {{ title }}\n\nSome *text* {{ title }}{{ /markdown }}</div>",
                "<h1>Hello there world</h1>\n<p>Some <em>text</em> Hello there world</p>",
            ],
            'indented markdown' => [
                "<div>{{ markdown:indent }}\n    # {{ title }}\n\n    Some *text* {{ title }}\n{{ /markdown:indent }}</div>",
                '<h1>Hello there world</h1>',
            ],
            'widont' => [
                '<p>{{ widont }}Some words {{ title }}{{ /widont }}</p>',
                '<p>Some words {{ title&nbsp;}}</p>',
            ],
        ];
    }

    public function test_obfuscated_content_never_contains_marker_text()
    {
        $data = ['email' => 'a@b.co'];
        $template = '<p>{{ obfuscate }}{{ email }}{{ /obfuscate }}</p>';
        $parser = $this->parser($data, true);
        $parser->setRuntimeConfiguration($this->runtimeConfiguration());

        $plain = (string) $parser->parse($template, $data);

        $this->assertSame('<p>{{ email }}</p>', html_entity_decode($plain));

        Runtime::register(HtmlInstrumentation::make());

        $instrumented = (string) $parser->parse($template, $data);
        $stripped = Runtime::removeMarkers($instrumented);

        $this->assertNotEmpty($this->markerExpressions($instrumented));
        $this->assertNotSame($stripped, $instrumented);
        $this->assertSame('<p>{{ email }}</p>', html_entity_decode($stripped));
    }
}
