<?php

namespace Tests\View\Instrumentation;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Contracts\View\Instrumentation\ComposesRenderedOutput;
use Statamic\Facades\Antlers;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Cascade;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationState;
use Statamic\View\Instrumentation\TagBody;
use Tests\Antlers\ParserTestCase;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;

class TagBodyInstrumentationTest extends ParserTestCase
{
    use DecodesMarkers, InteractsWithRuntimeConfigurations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['statamic.tags']['body'] = BodyProbeTag::class;
        $this->app['statamic.tags']['composer'] = BodyComposerTag::class;
        BodyProbeTag::$seen = [];
        BodyComposerTag::$seen = [];
    }

    private function render(string $template, ?HtmlInstrumentation $instrumenter, array $data = ['title' => 'Hello']): string
    {
        RuntimeParser::clearRenderNodeCache();
        $parser = $this->parser($data, true);
        $parser->cascade(app(Cascade::class));
        $parser->setRuntimeConfiguration($this->runtimeConfiguration());

        if ($instrumenter === null) {
            return (string) $parser->parse($template, $data);
        }

        Runtime::register($instrumenter);

        try {
            return (string) $parser->parse($template, $data);
        } finally {
            Runtime::forget($instrumenter);
        }
    }

    private function assertNeutral(string $template, HtmlInstrumentation $instrumenter): string
    {
        $plain = $this->render($template, null);
        $instrumented = $this->render($template, $instrumenter);

        $this->assertSame($plain, HtmlInstrumentation::removeMarkers($instrumented, [$instrumenter]), $template);
        $this->assertFalse(InstrumentationState::suppressed());

        return $instrumented;
    }

    private function titleMarkers(string $output, string $mode = 'comments'): array
    {
        if ($mode === 'attributes') {
            preg_match_all('/ data-antlers="([^"]+)"/', $output, $matches);

            $entries = [];

            foreach ($matches[1] as $payload) {
                foreach (HtmlInstrumentation::decode($payload) ?? [] as $entry) {
                    $entries[] = $entry;
                }
            }
        } else {
            $entries = $this->decodedMarkers($output);
        }

        return collect($entries)->where('expression', 'title')->values()->all();
    }

    public static function instrumenters(): array
    {
        return [
            'comments' => ['comments', fn () => HtmlInstrumentation::make()],
            'attributes' => ['attributes', fn () => HtmlInstrumentation::make()->withoutComments()->attributes('data-antlers')],
            'comments and attributes' => ['comments', fn () => HtmlInstrumentation::make()->attributes('data-antlers')],
        ];
    }

    #[DataProvider('instrumenters')]
    public function test_tags_see_marker_free_content_and_strings_while_untouched_renders_print_markers(string $mode, callable $make)
    {
        $template = '<div>{{ body:inspect }}<b>{{ title }}</b>{{ /body:inspect }}</div>';
        $output = $this->assertNeutral($template, $make());

        $this->assertSame('<b>{{ title }}</b>', BodyProbeTag::$seen['content']);
        $this->assertSame('<b>Hello</b>', BodyProbeTag::$seen['string']);

        $markers = $this->titleMarkers($output, $mode);

        $this->assertCount(1, $markers, $output);
        $this->assertSame(mb_strpos($template, '{{ title }}'), $markers[0]['start']);
        $this->assertSame('b', $markers[0]['element']);
    }

    #[DataProvider('instrumenters')]
    public function test_trimmed_bodies_keep_their_markers(string $mode, callable $make)
    {
        $template = "<ul>{{ body:trimmed }}\n  <li>{{ title }}</li>\n{{ /body:trimmed }}</ul>";
        $output = $this->assertNeutral($template, $make());

        $this->assertSame('<li>{{ title }}</li>', BodyProbeTag::$seen['content']);
        $this->assertSame(2, substr_count($output, '<li'));

        $markers = $this->titleMarkers($output, $mode);

        $this->assertCount(2, $markers, $output);
        $this->assertSame([mb_strpos($template, '{{ title }}')], collect($markers)->pluck('start')->unique()->all());
    }

    #[DataProvider('instrumenters')]
    public function test_wrapped_bodies_keep_their_markers_without_instrumenting_the_wrapper(string $mode, callable $make)
    {
        $template = '<div>{{ body:wrapped }}<b>{{ title }}</b>{{ /body:wrapped }}</div>';
        $output = $this->assertNeutral($template, $make());

        $this->assertStringContainsString('<i><b', $output);
        $this->assertStringNotContainsString('<i data-antlers', $output);

        $markers = $this->titleMarkers($output, $mode);

        $this->assertCount(1, $markers, $output);
        $this->assertSame(mb_strpos($template, '{{ title }}'), $markers[0]['start']);
    }

    #[DataProvider('instrumenters')]
    public function test_renders_read_as_strings_print_marker_free_unless_the_tag_asks_for_markers(string $mode, callable $make)
    {
        $composed = $this->assertNeutral('<div>{{ body:composed }}<b>{{ title }}</b>{{ /body:composed }}</div>', $make());

        $this->assertStringContainsString('<section><b>Hello</b></section>', $composed);
        $this->assertCount(0, $this->titleMarkers($composed, $mode), $composed);

        $transformed = $this->assertNeutral('<div>{{ body:transformed }}<b>{{ title }}</b>{{ /body:transformed }}</div>', $make());

        $this->assertStringContainsString('<B>HELLO</B>', $transformed);
        $this->assertCount(0, $this->titleMarkers($transformed, $mode), $transformed);

        $template = '<div>{{ composer }}<b>{{ title }}</b>{{ /composer }}</div>';
        $composer = $this->assertNeutral($template, $make());
        $markers = $this->titleMarkers($composer, $mode);

        $this->assertStringContainsString('<section><b', $composer);
        $this->assertCount(1, $markers, $composer);
        $this->assertSame(mb_strpos($template, '{{ title }}'), $markers[0]['start']);
        $this->assertNotSame('<b>{{ title }}</b>', BodyComposerTag::$seen['content']);
        $this->assertSame('<b>{{ title }}</b>', HtmlInstrumentation::removeMarkers(BodyComposerTag::$seen['content'], [$make()]));
    }

    public function test_string_templates_rendered_by_tags_keep_markers_only_when_returned_untouched()
    {
        $object = $this->assertNeutral('<div>{{ body:fresh_object }}</div>', HtmlInstrumentation::make());
        $markers = $this->titleMarkers($object);

        $this->assertCount(1, $markers, $object);
        $this->assertArrayNotHasKey('view', $markers[0]);
        $this->assertSame('u', $markers[0]['element']);

        $string = $this->assertNeutral('<div>{{ body:fresh_string }}</div>', HtmlInstrumentation::make());

        $this->assertStringContainsString('<u>Hello</u>', $string);
        $this->assertCount(0, $this->titleMarkers($string), $string);
    }

    public function test_bodies_in_unsafe_contexts_print_marker_free_output()
    {
        foreach (['inspect', 'trimmed', 'composed'] as $method) {
            $template = '<textarea>{{ body:'.$method.' }}<b>{{ title }}</b>{{ /body:'.$method.' }}</textarea>';

            $this->assertSame($this->render($template, null), $this->render($template, HtmlInstrumentation::make()), $method);
        }
        $this->assertFalse(InstrumentationState::suppressed());
    }

    public function test_sections_and_yield_defaults_keep_their_markers()
    {
        $template = '{{ section:main }}<b>{{ title }}</b>{{ /section:main }}<div>{{ yield:main }}</div><p>{{ yield:other }}<i>{{ title }}</i>{{ /yield:other }}</p>';
        $output = $this->assertNeutral($template, HtmlInstrumentation::make());

        $this->assertStringContainsString('<b><!-- antlers:start ', $output);
        $this->assertStringContainsString('<i><!-- antlers:start ', $output);
        $this->assertCount(2, $this->titleMarkers($output), $output);
        $this->assertSame(1, substr_count($output, '-->Hello<!-- antlers:end 1 --></b>'));
    }

    public function test_the_active_tag_body_is_released_when_a_tag_throws()
    {
        Runtime::register($instrumenter = HtmlInstrumentation::make());

        try {
            try {
                $this->render('<div>{{ body:throws }}<b>{{ title }}</b>{{ /body:throws }}</div>', HtmlInstrumentation::make());
                $this->fail('Expected the tag to throw.');
            } catch (\RuntimeException $exception) {
                $this->assertSame('body failed', $exception->getMessage());
            }

            $this->assertNull(InstrumentationState::activeTagBody());
        } finally {
            Runtime::forget($instrumenter);
        }

        $output = $this->assertNeutral('<div><b>{{ title }}</b></div>', HtmlInstrumentation::make());

        $this->assertCount(1, $this->titleMarkers($output), $output);
    }

    public function test_tag_bodies_align_derived_text_with_their_markers()
    {
        $instrumenter = HtmlInstrumentation::make();
        $start = '<!-- antlers:start YWJj -->';
        $end = '<!-- antlers:end 1 -->';
        $body = TagBody::from(" {$start}{{ a }}{$end} ", [$instrumenter]);

        $this->assertSame(' {{ a }} ', $body->clean);
        $this->assertSame(" {$start}{{ a }}{$end} ", $body->align(' {{ a }} '));
        $this->assertSame("{$start}{{ a }}{$end}", $body->align('{{ a }}'));
        $this->assertSame("<i> {$start}{{ a }}{$end} </i>", $body->align('<i> {{ a }} </i>'));
        $this->assertSame("{$start}{{ a }}{$end}", $body->align("{$start}{{ a }}{$end}"));
        $this->assertSame('<i> {{ a }} </i><i> {{ a }} </i>', $body->align('<i> {{ a }} </i><i> {{ a }} </i>'));
        $this->assertNull($body->align('{{ b }}'));
        $this->assertNull($body->align(''));

        $plain = TagBody::from('<b>{{ a }}</b>', [$instrumenter]);

        $this->assertSame('<b>{{ a }}</b>', $plain->align('<b>{{ a }}</b>'));
        $this->assertSame('{{ a }}', $plain->align('{{ a }}'));
        $this->assertNull($plain->align('<u>{{ b }}</u>'));
    }

    public function test_sliced_tag_bodies_only_keep_the_marker_pairs_they_contain()
    {
        $first = ['<!-- antlers:start YQ== -->', '<!-- antlers:end 1 -->'];
        $second = ['<!-- antlers:start Yg== -->', '<!-- antlers:end 2 -->'];
        $body = TagBody::from("<b>{$first[0]}{{ a }}{$first[1]}{$second[0]}{{ b }}{$second[1]}</b>", [HtmlInstrumentation::make()]);

        $this->assertSame('<b>{{ a }}{{ b }}</b>', $body->clean);
        $this->assertSame("{$first[0]}{{ a }}{$first[1]}", $body->align('{{ a }}'));
        $this->assertSame("{$second[0]}{{ b }}{$second[1]}", $body->align('{{ b }}'));
        $this->assertSame("{$first[0]}{{ a }}{$first[1]}{$second[0]}{{ b }}{$second[1]}</b>", $body->align('{{ a }}{{ b }}</b>'));
        $this->assertSame("a }}{$second[0]}{{ b }}{$second[1]}", $body->align('a }}{{ b }}'));
        $this->assertSame('<b>{{ a', $body->align('<b>{{ a'));
    }
}

class BodyProbeTag extends Tags
{
    public static array $seen = [];

    public function inspect()
    {
        static::$seen['content'] = $this->content;

        $parsed = $this->parse();

        static::$seen['string'] = (string) $parsed;

        return $parsed;
    }

    public function trimmed()
    {
        $this->content = trim($this->content);

        static::$seen['content'] = $this->content;

        return $this->parseLoop([['n' => 1], ['n' => 2]]);
    }

    public function wrapped()
    {
        $this->content = '<i>'.$this->content.'</i>';

        return $this->parse();
    }

    public function composed()
    {
        return '<section>'.$this->parse().'</section>';
    }

    public function transformed()
    {
        return strtoupper((string) $this->parse());
    }

    public function throws()
    {
        throw new \RuntimeException('body failed');
    }

    public function freshObject()
    {
        return Antlers::parse('<u>{{ title }}</u>', $this->context->all(), true);
    }

    public function freshString()
    {
        return (string) Antlers::parse('<u>{{ title }}</u>', $this->context->all(), true);
    }
}

class BodyComposerTag extends Tags implements ComposesRenderedOutput
{
    public static array $seen = [];

    public function index()
    {
        static::$seen['content'] = $this->content;

        return '<section>'.$this->parse().'</section>';
    }
}
