<?php

namespace Tests\View\Scaffolding;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter;
use Tests\TestCase;

class SourceEmitterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AntlersSourceEmitter::resetStack();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        AntlersSourceEmitter::resetStack();
    }

    #[Test]
    public function it_emits_a_self_closing_variable()
    {
        $emit = new AntlersSourceEmitter;

        $this->assertSame(
            '{{ title /}}',
            (string) $emit->variable('title')
        );
    }

    #[Test]
    public function it_emits_an_opening_variable()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->variable('title', selfClosing: false);

        $this->assertEquals('{{ title }}', (string) $result);
    }

    #[Test]
    public function it_emits_a_pair()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('container', fn ($e) => $e
            ->append('{{ nested_field }}')
        );

        $expected = <<<'ANTLERS'
{{ container }}
    {{ nested_field }}
{{ /container }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_multiple_variables()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->variables('title', 'url', 'id');

        $expected = <<<'ANTLERS'
{{ title /}}
{{ url /}}
{{ id /}}
ANTLERS;

        $result = StringUtilities::normalizeLineEndings($result);
        $expected = StringUtilities::normalizeLineEndings($expected);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_emits_comments()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->comment('This is a comment');

        $this->assertEquals('{{# This is a comment #}}', (string) $result);
    }

    #[Test]
    public function it_emits_conditional_branches()
    {
        $emit = new AntlersSourceEmitter;

        $branches = [
            ['condition' => 'type == "text"', 'template' => '{{ text }}'],
            ['condition' => 'type == "image"', 'template' => '{{ image }}'],
        ];

        $result = $emit->condition($branches);

        $expected = <<<'ANTLERS'
{{ if type == "text" }}
    {{ text }}
{{ elseif type == "image" }}
    {{ image }}
{{ /if }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_indents_content_in_pairs()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('wrapper', fn ($e) => $e
            ->append("{{ title }}\n{{ content }}")
        );

        $expected = <<<'ANTLERS'
{{ wrapper }}
    {{ title }}
    {{ content }}
{{ /wrapper }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_chains_methods_fluently()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('checkboxes', fn ($e) => $e
            ->variables('value', 'label')
        );

        $expected = <<<'ANTLERS'
{{ checkboxes }}
    {{ value /}}
    {{ label /}}
{{ /checkboxes }}
ANTLERS;

        $result = StringUtilities::normalizeLineEndings($result);
        $expected = StringUtilities::normalizeLineEndings($expected);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_appends_variables_with_options()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit
            ->variable('field')
            ->append(' ')
            ->variable('field:label');

        $expected = '{{ field /}} {{ field:label /}}';

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_adds_newlines()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit
            ->append('Line 1')
            ->newline()
            ->append('Line 2');

        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals("Line 1\nLine 2", $result);
    }

    #[Test]
    public function it_resets_after_converting_to_string()
    {
        $emit = new AntlersSourceEmitter;

        $emit->variable('title');
        $first = (string) $emit;

        $emit->variable('content');
        $second = (string) $emit;

        $this->assertEquals('{{ title /}}', $first);
        $this->assertEquals('{{ content /}}', $second);
        $this->assertNotEquals($first, $second);
    }

    #[Test]
    public function it_combines_comment_and_fields()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit
            ->variables('id', 'title')
            ->newline()
            ->comment('Available if asset exists:')
            ->variables('width', 'height');

        $expected = <<<'ANTLERS'
{{ id /}}
{{ title /}}

{{# Available if asset exists: #}}
{{ width /}}
{{ height /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_a_tag_with_parameters()
    {
        $emit = new AntlersSourceEmitter;
        $handle = 'groups';

        $result = $emit->tag('user:in', fn ($e) => $e
            ->comment('User belongs to the selected groups'), params: [':group' => "{{ {$handle} }}:handle"]);

        $expected = <<<'ANTLERS'
{{ user:in :group="{{ groups }}:handle" }}
    {{# User belongs to the selected groups #}}
{{ /user:in }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_outputs_raw_template_with_normalized_indentation()
    {
        $emit = new AntlersSourceEmitter;
        $handle = 'code';

        $result = $emit->raw(<<<ANTLERS
            {{ {$handle} }}
            <pre class="language-{{ mode }}">
              <code>{{ code }}</code>
            </pre>
            {{ /{$handle} }}
            ANTLERS);

        $expected = <<<'ANTLERS'
{{ code }}
<pre class="language-{{ mode }}">
    <code>{{ code }}</code>
</pre>
{{ /code }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_outputs_properties_from_root_variable()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->properties('asset', ['url', 'title', 'alt']);

        $expected = <<<'ANTLERS'
{{ asset:url /}}
{{ asset:title /}}
{{ asset:alt /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_outputs_nested_properties_with_dot_notation()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->properties('item', ['meta.author', 'meta.date', 'title']);

        $expected = <<<'ANTLERS'
{{ item:meta:author /}}
{{ item:meta:date /}}
{{ item:title /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_outputs_array_keys_with_keys_method()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->keys('config', ['theme', 'locale', 'timezone']);

        $expected = <<<'ANTLERS'
{{ config['theme'] /}}
{{ config['locale'] /}}
{{ config['timezone'] /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_supports_bracket_notation_in_properties()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->properties('item', [
            'title',
            '[meta]',
            'author.name',
        ]);

        $expected = <<<'ANTLERS'
{{ item:title /}}
{{ item['meta'] /}}
{{ item:author:name /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_supports_nested_bracket_notation()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->properties('item', [
            '[config][theme]',
            '[settings][locale]',
            'title',
        ]);

        $expected = <<<'ANTLERS'
{{ item['config']['theme'] /}}
{{ item['settings']['locale'] /}}
{{ item:title /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_supports_mixed_property_and_array_access()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->properties('item', [
            'meta[author]',
            'settings.theme[color]',
            'data[info][title]',
        ]);

        $expected = <<<'ANTLERS'
{{ item:meta['author'] /}}
{{ item:settings:theme['color'] /}}
{{ item:data['info']['title'] /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_chains_keys_and_properties_in_pairs()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('items', fn ($e) => $e
            ->properties('', ['title', 'description'])
            ->keys('', ['type', 'status'])
        );

        $expected = <<<'ANTLERS'
{{ items }}
    {{ title /}}
    {{ description /}}
    {{ ['type'] /}}
    {{ ['status'] /}}
{{ /items }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_uses_properties_within_complex_structures()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('images', fn ($e) => $e
            ->comment('Image details')
            ->properties('', ['url', 'alt', 'width', 'height'])
        );

        $expected = <<<'ANTLERS'
{{ images }}
    {{# Image details #}}
    {{ url /}}
    {{ alt /}}
    {{ width /}}
    {{ height /}}
{{ /images }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_with_context_using_stack()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->withContext('author', fn ($e) => $e
            ->variables('name', 'email')
        );

        $expected = <<<'ANTLERS'
{{ author:name /}}
{{ author:email /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_nested_with_context_scopes()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('items', fn ($e) => $e
            ->variable('title')
            ->append($e->withContext('meta', fn ($e2) => $e2
                ->variables('author', 'date')
                ->append($e2->withContext('settings', fn ($e3) => $e3
                    ->variables('theme', 'locale')
                ))
            ))
        );

        $expected = <<<'ANTLERS'
{{ items }}
    {{ title /}}
    {{ meta:author /}}
    {{ meta:date /}}
    {{ meta:settings:theme /}}
    {{ meta:settings:locale /}}
{{ /items }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_combines_with_context_and_other_methods()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('entries', fn ($e) => $e
            ->variable('title')
            ->append($e->withContext('author', fn ($e2) => $e2
                ->comment('Author information')
                ->properties('', ['name', 'email', 'bio'])
            ))
        );

        $expected = <<<'ANTLERS'
{{ entries }}
    {{ title /}}
    {{# Author information #}}
    {{ author:name /}}
    {{ author:email /}}
    {{ author:bio /}}
{{ /entries }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_isolates_variables_from_context_stack()
    {
        $emit = new AntlersSourceEmitter;

        $emit->pushContext('items', isIteration: true);

        $result = $emit->isolate(fn ($e) => $e
            ->variables('standalone', 'variable')
        );

        $expected = <<<'ANTLERS'
{{ standalone /}}
{{ variable /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_isolates_within_nested_contexts()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('items', fn ($e) => $e
            ->variable('title')
            ->newline()
            ->append($e->isolate(fn ($e2) => $e2
                ->comment('These variables are isolated')
                ->variables('global_var', 'another_global')
            ))
            ->newline()
            ->variable('description')
        );

        $expected = <<<'ANTLERS'
{{ items }}
    {{ title /}}
    {{# These variables are isolated #}}
    {{ global_var /}}
    {{ another_global /}}
    {{ description /}}
{{ /items }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_restores_context_after_isolate()
    {
        $emit = new AntlersSourceEmitter;

        $emit->pushContext('items', isIteration: true);

        $isolated = $emit->isolate(fn ($e) => $e->variable('standalone'));

        $emit->variable('contextual');
        $normal = (string) $emit;

        $this->assertEquals('{{ standalone /}}', StringUtilities::normalizeLineEndings($isolated));
        $this->assertEquals('{{ items:contextual /}}', StringUtilities::normalizeLineEndings($normal));
    }

    #[Test]
    public function it_handles_nested_isolate_calls()
    {
        $emit = new AntlersSourceEmitter;

        $emit->pushContext('outer', isIteration: true);

        $result = $emit->isolate(fn ($e) => $e
            ->variable('first')
            ->append($e->isolate(fn ($e2) => $e2
                ->variable('second')
            ))
        );

        $expected = <<<'ANTLERS'
{{ first /}}{{ second /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }
}
