<?php

namespace Tests\View\Instrumentation;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\FieldDomTracer;
use Statamic\View\Instrumentation\FieldMarkers;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class FieldMarkersInstrumentationTest extends ParserTestCase
{
    use FakesViews;

    #[DataProvider('templates')]
    public function test_field_markers_never_change_what_a_template_renders(array $views, string $expected)
    {
        [$plain, $instrumented] = $this->renderWithAndWithoutFieldMarkers($views);

        $this->assertSame($expected, $plain);
        $this->assertNotSame($plain, $instrumented);
        $this->assertSame($plain, Runtime::removeMarkers($instrumented));
    }

    public static function templates(): array
    {
        $inner = '<em>{{ title }}</em>';

        return [
            'comment regions' => [
                ['page' => '<p>{{ title }}</p>'],
                '<p>Hello</p>',
            ],
            'attribute regions' => [
                ['page' => '<input value="{{ title }}">'],
                '<input value="Hello">',
            ],
            'tags that post-process their body' => [
                ['page' => '<p>{{ widont }}Some words {{ title }}{{ /widont }}</p>'],
                '<p>Some words {{ title&nbsp;}}</p>',
            ],
            'tags that echo their body' => [
                ['page' => '<p>{{ echo_body }}{{ title }}{{ /echo_body }}</p>'],
                '<p>{{ title }}</p>',
            ],
            'slots can be modified' => [
                [
                    'card' => '<div data-length="{{ slot | length }}">{{ slot | truncate(8) }}</div>',
                    'page' => '<section>{{ partial:card }}<b>{{ title }}</b>{{ /partial:card }}</section>',
                ],
                '<section><div data-length="12"><b>Hello</div></section>',
            ],
            'assigned views can be compared' => [
                ['inner' => $inner, 'page' => '{{ view = {partial:inner} }}<p>{{ if view == "<em>Hello</em>" }}match{{ else }}different{{ /if }}</p>'],
                '<p>match</p>',
            ],
            'interpolated views can be parameters' => [
                ['inner' => $inner, 'echo' => '<p title="{{ label | strip_tags }}">{{ label | length }}</p>', 'page' => '<div>{{ partial:echo label="{partial:inner}" }}</div>'],
                '<div><p title="Hello">14</p></div>',
            ],
            'the antlers modifier can be chained' => [
                ['page' => '<div>{{ snippet | antlers | length }}</div>'],
                '<div>12</div>',
            ],
        ];
    }

    public function test_obfuscated_content_never_contains_helper_tags()
    {
        [$plain, $instrumented] = $this->renderWithAndWithoutFieldMarkers(['page' => '<p>{{ obfuscate }}{{ title }}{{ /obfuscate }}</p>']);

        $this->assertSame('<p>{{ title }}</p>', html_entity_decode($plain));
        $this->assertStringNotContainsString('internal_field', html_entity_decode($instrumented));
        $this->assertSame('<p>{{ title }}</p>', html_entity_decode(Runtime::removeMarkers($instrumented)));
    }

    public function test_a_mismatched_end_unwinds_to_its_frame()
    {
        $tracer = new FieldDomTracer;
        $outer = $tracer->instruction('start');
        $inner = $tracer->instruction('start');
        $outerEnd = $tracer->instruction('end', $outer);
        $innerEnd = $tracer->instruction('end', $inner);

        $rendered = $tracer->capture(fn () => implode('', [
            FieldDomTracer::execute($outer),
            FieldDomTracer::execute($inner),
            FieldDomTracer::execute($outerEnd),
            FieldDomTracer::execute($innerEnd),
        ]));

        $this->assertSame('<!--statamic:field:1--><!--statamic:field:2--><!--/statamic:field:1-->', $rendered);
    }

    private function renderWithAndWithoutFieldMarkers(array $views): array
    {
        $this->app['statamic.tags']['echo_body'] = EchoBodyTag::class;
        $this->withFakeViews();

        foreach ($views as $name => $contents) {
            $this->viewShouldReturnRaw($name, $contents);
        }

        $data = ['title' => 'Hello', 'snippet' => '<b>{{ title }}</b>'];
        $plain = view('page', $data)->render();

        $tracer = new FieldDomTracer;
        Runtime::register(FieldMarkers::make()->using($tracer));

        return [$plain, $tracer->capture(fn () => view('page', $data)->render())];
    }
}

class EchoBodyTag extends Tags
{
    public function index()
    {
        return $this->content;
    }
}
