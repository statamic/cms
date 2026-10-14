<?php

namespace Tests\View\Instrumentation;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;

class InstrumentationNeutralityTest extends ParserTestCase
{
    use DecodesMarkers, FakesViews;

    #[DataProvider('templates')]
    public function test_markers_never_change_what_a_template_renders(array $views, string $expected)
    {
        [$plain, $instrumented] = $this->renderWithAndWithoutInstrumentation($views);

        $this->assertSame($expected, $plain);
        $this->assertStringContainsString('<!-- antlers:start ', $instrumented);
        $this->assertSame($plain, Runtime::removeMarkers($instrumented));
    }

    public static function templates(): array
    {
        $card = '<div class="card">{{ if slot }}<div class="body">{{ slot }}</div>{{ else }}<div class="empty">empty</div>{{ /if }}</div>';
        $inner = '<em>{{ title }}</em>';

        return [
            'an empty slot stays empty' => [
                ['card' => $card, 'page' => '<section>{{ partial:card }}{{ if show }}<b>{{ title }}</b>{{ /if }}{{ /partial:card }}</section>'],
                '<section><div class="card"><div class="empty">empty</div></div></section>',
            ],
            'an empty named slot stays empty' => [
                [
                    'card' => '<div>{{ if slot:footer }}<footer>{{ slot:footer }}</footer>{{ else }}<em>none</em>{{ /if }}</div>',
                    'page' => '<section>{{ partial:card }}{{ slot:footer }}{{ if show }}<i>{{ title }}</i>{{ /if }}{{ /slot:footer }}{{ /partial:card }}</section>',
                ],
                '<section><div><em>none</em></div></section>',
            ],
            'slots can be modified' => [
                [
                    'card' => '<div data-length="{{ slot | length }}">{{ slot | truncate(8) }}</div>',
                    'page' => '<section>{{ partial:card }}<b>{{ title }}</b>{{ /partial:card }}</section>',
                ],
                '<section><div data-length="12"><b>Hello</div></section>',
            ],
            'named slots can be modified' => [
                [
                    'card' => '<div>{{ slot:footer | upper }}</div>',
                    'page' => '<section>{{ partial:card }}{{ slot:footer }}<i>{{ title }}</i>{{ /slot:footer }}{{ /partial:card }}</section>',
                ],
                '<section><div><I>HELLO</I></div></section>',
            ],
            'slot whitespace is trimmed through markers' => [
                ['card' => '<div>[{{ slot }}]</div>', 'page' => "<section>{{ partial:card }}\n  {{ if title }}\n    <b>{{ title }}</b>\n  {{ /if }}\n{{ /partial:card }}</section>"],
                '<section><div>[<b>Hello</b>]</div></section>',
            ],
            'empty include slots render the same' => [
                ['card' => $card, 'page' => '<section>{{ include:card }}{{ if show }}<b>{{ title }}</b>{{ /if }}{{ /include:card }}</section>'],
                '<section><div class="card"><div class="body"></div></div></section>',
            ],
            'include slots can be modified' => [
                ['card' => '<div>{{ slot | upper }}</div>', 'page' => '<section>{{ include:card }}<b>{{ title }}</b>{{ /include:card }}</section>'],
                '<section><div><B>HELLO</B></div></section>',
            ],
            'interpolated views can be modified' => [
                ['inner' => $inner, 'page' => '<div>{{ {partial:inner} | truncate(8) }}</div>'],
                '<div><em>Hell</div>',
            ],
            'assigned views can be compared' => [
                ['inner' => $inner, 'page' => '{{ view = {partial:inner} }}<p>{{ if view == "<em>Hello</em>" }}match{{ else }}different{{ /if }}</p>'],
                '<p>match</p>',
            ],
            'interpolated views can be parameters' => [
                ['inner' => $inner, 'echo' => '<p title="{{ label | strip_tags }}">{{ label | length }}</p>', 'page' => '<div>{{ partial:echo label="{partial:inner}" }}</div>'],
                '<div><p title="Hello">14</p></div>',
            ],
            'antlers fields can be modified' => [
                ['page' => '<div>{{ intro | truncate(14) }}</div><p>{{ if intro == "Read <b>Hello</b> now" }}match{{ else }}different{{ /if }}</p>'],
                '<div>Read <b>Hello<</div><p>match</p>',
            ],
            'the antlers modifier can be chained' => [
                ['page' => '<div>{{ snippet | antlers | length }}</div><p>{{ snippet | antlers | upper }}</p>'],
                '<div>12</div><p><B>HELLO</B></p>',
            ],
            'line endings follow the authored template' => [
                ['page' => "<pre>{{ body }}</pre>\n<p>{{ title }}</p><p>{{ subtitle }}</p>"],
                "<pre>one\ntwo</pre>\n<p>Hello</p><p>World</p>",
            ],
        ];
    }

    public function test_slots_printed_as_element_content_keep_their_markers()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('card', '<div title="{{ slot }}">{{ if slot }}<p>{{ slot }}</p><aside>{{ slot:footer }}</aside>{{ /if }}</div>');
        $this->viewShouldReturnRaw('page', "<section>{{ partial:card }}\n{{ if title }} <b>{{ title }}</b> {{ /if }}\n{{ slot:footer }}<i>{{ title }}</i>{{ /slot:footer }}{{ /partial:card }}</section>");
        Runtime::register(HtmlInstrumentation::make());

        $rendered = view('page', ['title' => 'Hello'])->render();

        preg_match('~<p>(.*)</p><aside>(.*)</aside>~s', $rendered, $printed);

        $this->assertStringContainsString('<div title="<b>Hello</b>">', $rendered);
        $this->assertSame(['slot', 'if title', 'title', 'slot:footer'], $this->markerExpressions($printed[1]));
        $this->assertSame(['slot:footer', 'title'], $this->markerExpressions($printed[2]));
        $this->assertSame(
            '<section><div title="<b>Hello</b>"><p><b>Hello</b></p><aside><i>Hello</i></aside></div></section>',
            Runtime::removeMarkers($rendered)
        );
    }

    public function test_user_content_is_never_instrumented()
    {
        config(['statamic.antlers.allowedContentTags' => ['partial:*']]);
        $this->withFakeViews();
        $this->viewShouldReturnRaw('inner', '<em>{{ title }}</em>');
        $this->viewShouldReturnRaw('page', '<div>{{ intro }}</div><p>{{ partial:inner }}</p>');
        Runtime::register(HtmlInstrumentation::make());

        $intro = new Value('Read <b>{{ title }}</b> {{ partial:inner }}', 'intro', (new Text)->setField(new Field('intro', ['type' => 'text', 'antlers' => true])));
        $rendered = view('page', ['title' => 'Hello', 'intro' => $intro])->render();

        $this->assertSame(['intro', 'partial:inner', 'title'], $this->markerExpressions($rendered));
        $this->assertStringContainsString('-->Read <b>Hello</b> <em>Hello</em><!-- antlers:end 1 -->', $rendered);
        $this->assertSame(
            '<div>Read <b>Hello</b> <em>Hello</em></div><p><em>Hello</em></p>',
            Runtime::removeMarkers($rendered)
        );

        $template = '<p>{{ title }}</p>';

        $this->assertSame('<p>Hello</p>', (string) Antlers::parse($template, ['title' => 'Hello']));
        $this->assertStringContainsString('<p><!-- antlers:start ', (string) Antlers::parse($template, ['title' => 'Hello'], true));
    }

    private function renderWithAndWithoutInstrumentation(array $views): array
    {
        $this->withFakeViews();

        foreach ($views as $name => $contents) {
            $this->viewShouldReturnRaw($name, $contents);
        }

        $data = fn () => [
            'title' => 'Hello',
            'subtitle' => 'World',
            'show' => false,
            'body' => "one\ntwo",
            'snippet' => '<b>{{ title }}</b>',
            'intro' => new Value('Read <b>{{ title }}</b> now', 'intro', (new Text)->setField(new Field('intro', ['type' => 'text', 'antlers' => true]))),
        ];

        $plain = view('page', $data())->render();

        Runtime::register(HtmlInstrumentation::make());

        return [$plain, view('page', $data())->render()];
    }
}
