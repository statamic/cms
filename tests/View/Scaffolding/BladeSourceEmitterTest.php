<?php

namespace Tests\View\Scaffolding;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Scaffolding\Emitters\BladeSourceEmitter;
use Tests\TestCase;

class BladeSourceEmitterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        BladeSourceEmitter::resetStack();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        BladeSourceEmitter::resetStack();
    }

    #[Test]
    public function it_emits_a_variable()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->variable('title');

        $this->assertEquals('{{ $title }}', (string) $result);
    }

    #[Test]
    public function it_emits_multiple_variables()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->variables('title', 'url', 'id');

        $expected = <<<'BLADE'
{{ $title }}
{{ $url }}
{{ $id }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_a_pair_using_blade_components()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->pair('users', fn ($e) => $e
            ->variables('name', 'email')
        );

        $expected = <<<'BLADE'
<s:users>
    {{ $name }}
    {{ $email }}
</s:users>
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_comments()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->comment('This is a comment');

        $this->assertEquals('{{-- This is a comment --}}', (string) $result);
    }

    #[Test]
    public function it_emits_conditional_branches()
    {
        $emit = new BladeSourceEmitter;

        $branches = [
            ['condition' => '$errors', 'template' => '{{ $message }}'],
            ['condition' => '$success', 'template' => '{{ $success }}'],
        ];

        $result = $emit->condition($branches);

        $expected = <<<'BLADE'
@if($errors)
    {{ $message }}
@elseif($success)
    {{ $success }}
@endif
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_outputs_raw_template_with_normalized_indentation()
    {
        $emit = new BladeSourceEmitter;
        $handle = 'users';

        $result = $emit->raw(<<<BLADE
            <s:{$handle}>
                @if(\$active)
                    {{ \$name }}
                @endif
            </s:{$handle}>
            BLADE);

        $expected = <<<'BLADE'
<s:users>
    @if($active)
        {{ $name }}
    @endif
</s:users>
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_chains_methods_fluently()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->pair('users', fn ($e) => $e
            ->comment('List all active users')
            ->variables('name', 'email')
        );

        $expected = <<<'BLADE'
<s:users>
    {{-- List all active users --}}
    {{ $name }}
    {{ $email }}
</s:users>
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_manages_variable_context_stack()
    {
        $emit = new BladeSourceEmitter;

        $this->assertNull($emit->getCurrentRoot());
        $this->assertEmpty($emit->getVariableStack());

        $emit->pushContext('item', isIteration: false);
        $this->assertEquals('item', $emit->getCurrentRoot());
        $this->assertCount(1, $emit->getVariableStack());

        $emit->popContext();
        $this->assertNull($emit->getCurrentRoot());
        $this->assertEmpty($emit->getVariableStack());
    }

    #[Test]
    public function it_outputs_context_aware_variables()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->variable('title');
        $this->assertEquals('{{ $title }}', (string) $result);

        $emit = new BladeSourceEmitter;
        $emit->pushContext('item', isIteration: true);
        $result = $emit->variable('title');
        $this->assertEquals('{{ $item->title }}', (string) $result);
    }

    #[Test]
    public function it_handles_nested_contexts()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('items', isIteration: true);
        $emit->pushContext('meta', isIteration: false);

        $result = $emit->variable('author');
        $this->assertEquals('{{ $items->meta->author }}', (string) $result);

        $emit->popContext();
        $result = $emit->variable('title');
        $this->assertEquals('{{ $items->title }}', (string) $result);
    }

    #[Test]
    public function it_builds_dot_notation_paths()
    {
        $emit = new BladeSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->variable('meta.author.name');
        $this->assertEquals('{{ $item->meta->author->name }}', (string) $result);
    }

    #[Test]
    public function it_emits_foreach_with_automatic_context()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->variable('title')
            ->variable('description')
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    {{ $item->title }}
    {{ $item->description }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_nested_foreach_with_contexts()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('grid', 'row', null, fn ($e) => $e
            ->append($e->forEach('items', 'item', null, fn ($e2) => $e2
                ->variable('name')
            ))
        );

        $expected = <<<'BLADE'
@foreach ($grid as $row)
    @foreach ($row->items as $item)
        {{ $item->name }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_pair_with_context()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->append($e->pairWithContext('author', fn ($e2) => $e2
                ->variable('name')
                ->variable('email')
            ))
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    <s:author>
        {{ $item->author->name }}
        {{ $item->author->email }}
    </s:author>
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_complex_nested_structures()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('grid', 'row', null, fn ($e) => $e
            ->variable('title')
            ->append($e->pairWithContext('meta', fn ($e2) => $e2
                ->variable('author')
                ->variable('date')
            ))
            ->append($e->forEach('tags', 'tag', null, fn ($e2) => $e2
                ->variable('name')
            ))
        );

        $expected = <<<'BLADE'
@foreach ($grid as $row)
    {{ $row->title }}
    <s:meta>
        {{ $row->meta->author }}
        {{ $row->meta->date }}
    </s:meta>
    @foreach ($row->tags as $tag)
        {{ $tag->name }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_multiple_iteration_contexts()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('outer', isIteration: true);
        $this->assertEquals('outer', $emit->getCurrentRoot());

        $emit->pushContext('inner', isIteration: true);
        $this->assertEquals('inner', $emit->getCurrentRoot());

        $emit->popContext();
        $this->assertEquals('outer', $emit->getCurrentRoot());
    }

    #[Test]
    public function it_inherits_context_in_pair_without_modification()
    {
        $emit = new BladeSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->pair('wrapper', fn ($e) => $e
            ->variable('title')
        );

        $expected = <<<'BLADE'
<s:wrapper>
    {{ $item->title }}
</s:wrapper>
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_supports_variables_method_with_context()
    {
        $emit = new BladeSourceEmitter;
        $emit->pushContext('row', isIteration: true);

        $result = $emit->variables('title', 'description', 'url');

        $expected = <<<'BLADE'
{{ $row->title }}
{{ $row->description }}
{{ $row->url }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_outputs_properties_from_root_variable()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->properties('asset', ['url', 'title', 'alt']);

        $expected = <<<'BLADE'
{{ $asset->url }}
{{ $asset->title }}
{{ $asset->alt }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_outputs_properties_with_context_awareness()
    {
        $emit = new BladeSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->properties('asset', ['url', 'width', 'height']);

        $expected = <<<'BLADE'
{{ $item->asset->url }}
{{ $item->asset->width }}
{{ $item->asset->height }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_uses_properties_in_foreach_loops()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('images', 'image', null, fn ($e) => $e
            ->properties('image', ['url', 'alt', 'title'])
        );

        $expected = <<<'BLADE'
@foreach ($images as $image)
    {{ $image->url }}
    {{ $image->alt }}
    {{ $image->title }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_chains_properties_with_other_methods()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('assets', 'asset', null, fn ($e) => $e
            ->comment('Asset details')
            ->properties('asset', ['url', 'filename', 'size'])
            ->variable('asset.container')
        );

        $expected = <<<'BLADE'
@foreach ($assets as $asset)
    {{-- Asset details --}}
    {{ $asset->url }}
    {{ $asset->filename }}
    {{ $asset->size }}
    {{ $asset->container }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_nested_property_paths_in_properties()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->properties(
            'item',
            [
                'meta.author',
                'meta.date',
                'title',
            ]
        );

        $expected = <<<'BLADE'
{{ $item->meta->author }}
{{ $item->meta->date }}
{{ $item->title }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_outputs_array_keys_with_keys_method()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->keys('config', ['theme', 'locale', 'timezone']);

        $expected = <<<'BLADE'
{{ $config['theme'] }}
{{ $config['locale'] }}
{{ $config['timezone'] }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_outputs_keys_with_context_awareness()
    {
        $emit = new BladeSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->keys('settings', ['color', 'size']);

        $expected = <<<'BLADE'
{{ $item->settings['color'] }}
{{ $item->settings['size'] }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_uses_keys_in_foreach_loops()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->keys('item', ['name', 'value', 'label'])
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    {{ $item['name'] }}
    {{ $item['value'] }}
    {{ $item['label'] }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_supports_bracket_notation_in_properties()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->properties('item', [
            'title',
            '[meta]',
            'author.name',
        ]);

        $expected = <<<'BLADE'
{{ $item->title }}
{{ $item['meta'] }}
{{ $item->author->name }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_supports_nested_bracket_notation()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->properties('item', [
            '[config][theme]',
            '[settings][locale]',
            'title',
        ]);

        $expected = <<<'BLADE'
{{ $item['config']['theme'] }}
{{ $item['settings']['locale'] }}
{{ $item->title }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_supports_mixed_property_and_array_access()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->properties('item', [
            'meta[author]',
            'settings.theme[color]',
            'data[info][title]',
        ]);

        $expected = <<<'BLADE'
{{ $item->meta['author'] }}
{{ $item->settings->theme['color'] }}
{{ $item->data['info']['title'] }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_handles_complex_mixed_notation_with_context()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->properties('item', [
                'title',
                'meta[author]',
                '[config][theme]',
                'settings.locale',
            ])
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    {{ $item->title }}
    {{ $item->meta['author'] }}
    {{ $item['config']['theme'] }}
    {{ $item->settings->locale }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_chains_keys_and_properties_together()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->properties('item', ['title', 'description'])
            ->keys('item', ['type', 'status'])
            ->variable('item.id')
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    {{ $item->title }}
    {{ $item->description }}
    {{ $item['type'] }}
    {{ $item['status'] }}
    {{ $item->id }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_nested_with_context_calls()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->variable('title')
        );

        $expected = '{{ $root1->title }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_double_nested_with_context()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->append($e->withContext('root2', fn ($e2) => $e2
                ->variable('final')
            ))
        );

        $expected = '{{ $root1->root2->final }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_triple_nested_with_context()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->append($e->withContext('root2', fn ($e2) => $e2
                ->append($e2->withContext('root3', fn ($e3) => $e3
                    ->variable('final')
                ))
            ))
        );

        $expected = '{{ $root1->root2->root3->final }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_nested_with_context_with_multiple_variables()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->variable('title')
            ->append($e->withContext('root2', fn ($e2) => $e2
                ->variable('name')
                ->variable('description')
                ->append($e2->withContext('root3', fn ($e3) => $e3
                    ->variable('value')
                ))
            ))
        );

        $expected = <<<'BLADE'
{{ $root1->title }}
{{ $root1->root2->name }}
{{ $root1->root2->description }}
{{ $root1->root2->root3->value }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_nested_with_context_inside_foreach()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->variable('title')
            ->append($e->withContext('meta', fn ($e2) => $e2
                ->variable('author')
                ->append($e2->withContext('details', fn ($e3) => $e3
                    ->variable('bio')
                ))
            ))
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    {{ $item->title }}
    {{ $item->meta->author }}
    {{ $item->meta->details->bio }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_with_context_after_popping_maintains_correct_path()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->append($e->withContext('root2', fn ($e2) => $e2
                ->variable('inner')
            ))
            ->variable('outer')
        );

        $expected = <<<'BLADE'
{{ $root1->root2->inner }}
{{ $root1->outer }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_parallel_nested_contexts()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->append($e->withContext('branch1', fn ($e2) => $e2
                ->variable('value1')
            ))
            ->append($e->withContext('branch2', fn ($e2) => $e2
                ->variable('value2')
            ))
        );

        $expected = <<<'BLADE'
{{ $root1->branch1->value1 }}
{{ $root1->branch2->value2 }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_respects_context_with_dot_notation_properties()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->append($e->withContext('root2', fn ($e2) => $e2
                ->variable('meta.author.name')
            ))
        );

        $expected = '{{ $root1->root2->meta->author->name }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_generates_first_counted_variable_without_suffix()
    {
        $emit = new BladeSourceEmitter;

        $var = $emit->getCountedVariable('set');

        $this->assertEquals('set', $var);
        $this->assertEquals(0, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_generates_counted_variables_with_incrementing_suffixes()
    {
        $emit = new BladeSourceEmitter;

        $var1 = $emit->getCountedVariable('set');
        $var2 = $emit->getCountedVariable('set');
        $var3 = $emit->getCountedVariable('set');

        $this->assertEquals('set', $var1);
        $this->assertEquals('set1', $var2);
        $this->assertEquals('set2', $var3);
        $this->assertEquals(2, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_releases_counted_variables_by_decrementing()
    {
        $emit = new BladeSourceEmitter;

        $var1 = $emit->getCountedVariable('set');
        $var2 = $emit->getCountedVariable('set');
        $var3 = $emit->getCountedVariable('set');

        $this->assertEquals(2, $emit->getVariableCounter('set'));

        $emit->releaseCountedVariable('set');
        $this->assertEquals(1, $emit->getVariableCounter('set'));

        $emit->releaseCountedVariable('set');
        $this->assertEquals(0, $emit->getVariableCounter('set'));

        $emit->releaseCountedVariable('set');
        $this->assertEquals(-1, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_tracks_multiple_variable_counters_independently()
    {
        $emit = new BladeSourceEmitter;

        $set1 = $emit->getCountedVariable('set');
        $item1 = $emit->getCountedVariable('item');
        $set2 = $emit->getCountedVariable('set');
        $item2 = $emit->getCountedVariable('item');

        $this->assertEquals('set', $set1);
        $this->assertEquals('item', $item1);
        $this->assertEquals('set1', $set2);
        $this->assertEquals('item1', $item2);

        $this->assertEquals(1, $emit->getVariableCounter('set'));
        $this->assertEquals(1, $emit->getVariableCounter('item'));
    }

    #[Test]
    public function it_resets_counters_with_reset_stack()
    {
        $emit = new BladeSourceEmitter;

        $emit->getCountedVariable('set');
        $emit->getCountedVariable('set');
        $emit->getCountedVariable('item');

        $this->assertEquals(1, $emit->getVariableCounter('set'));
        $this->assertEquals(0, $emit->getVariableCounter('item'));

        BladeSourceEmitter::resetStack();

        $this->assertEquals(-1, $emit->getVariableCounter('set'));
        $this->assertEquals(-1, $emit->getVariableCounter('item'));
    }

    #[Test]
    public function it_gets_all_variable_counters()
    {
        $emit = new BladeSourceEmitter;

        $emit->getCountedVariable('set');
        $emit->getCountedVariable('set');
        $emit->getCountedVariable('item');
        $emit->getCountedVariable('row');

        $counters = $emit->getVariableCounters();

        $this->assertEquals([
            'set' => 1,
            'item' => 0,
            'row' => 0,
        ], $counters);
    }

    #[Test]
    public function it_handles_variable_with_counted_closure_and_auto_releases()
    {
        $emit = new BladeSourceEmitter;

        $capturedVar = null;

        $result = $emit->withCountedVariable('set', function ($varName) use (&$capturedVar) {
            $capturedVar = $varName;

            return 'result';
        });

        $this->assertEquals('set', $capturedVar);
        $this->assertEquals('result', $result);
        $this->assertEquals(-1, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_releases_counted_variable_even_on_exception()
    {
        $emit = new BladeSourceEmitter;

        try {
            $emit->withCountedVariable('set', function ($varName) {
                throw new \Exception('Test exception');
            });
        } catch (\Exception $e) {
        }

        $this->assertEquals(-1, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_supports_nested_with_counted_variable_calls()
    {
        $emit = new BladeSourceEmitter;

        $vars = [];

        $emit->withCountedVariable('set', function ($var1) use (&$vars, $emit) {
            $vars[] = $var1;

            $emit->withCountedVariable('set', function ($var2) use (&$vars, $emit) {
                $vars[] = $var2;

                $emit->withCountedVariable('set', function ($var3) use (&$vars) {
                    $vars[] = $var3;
                });
            });
        });

        $this->assertEquals(['set', 'set1', 'set2'], $vars);
        $this->assertEquals(-1, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_handles_nested_foreach_with_counted_variables()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withCountedVariable('set', function ($setVar1) use ($emit) {
            return $emit->forEach('grid', $setVar1, null, function ($e) use ($emit) {
                return $e->variable('title')->append(
                    $emit->withCountedVariable('set', function ($setVar2) use ($emit) {
                        return $emit->forEach('items', $setVar2, null, function ($e2) {
                            return $e2->variable('name');
                        });
                    })
                );
            });
        });

        $expected = <<<'BLADE'
@foreach ($grid as $set)
    {{ $set->title }}
    @foreach ($set->items as $set1)
        {{ $set1->name }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_generates_triple_nested_foreach_with_counted_variables()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withCountedVariable('set', function ($setVar1) use ($emit) {
            return $emit->forEach('grid', $setVar1, null, function ($e) use ($emit) {
                return $emit->withCountedVariable('set', function ($setVar2) use ($emit) {
                    return $emit->forEach('rows', $setVar2, null, function ($e2) use ($emit) {
                        return $emit->withCountedVariable('set', function ($setVar3) use ($emit) {
                            return $emit->forEach('items', $setVar3, null, function ($e3) {
                                return $e3->variable('value');
                            });
                        });
                    });
                });
            });
        });

        $expected = <<<'BLADE'
@foreach ($grid as $set)
    @foreach ($set->rows as $set1)
        @foreach ($set1->items as $set2)
            {{ $set2->value }}
        @endforeach
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_creates_isolated_context_with_fresh_variable_stack()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('outer', isIteration: true);

        $result = $emit->withIsolatedContext('inner', function ($e) {
            return $e->variable('title');
        }, isIteration: true);

        $expected = '{{ $inner->title }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);

        $this->assertEquals('outer', $emit->getCurrentRoot());
    }

    #[Test]
    public function it_restores_stack_after_isolated_context()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('original', isIteration: true);
        $this->assertEquals('original', $emit->getCurrentRoot());

        $emit->withIsolatedContext('isolated', function ($e) {
            $this->assertEquals('isolated', $e->getCurrentRoot());

            return $e->variable('test');
        }, isIteration: true);

        $this->assertEquals('original', $emit->getCurrentRoot());
    }

    #[Test]
    public function it_handles_nested_isolated_contexts()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withIsolatedContext('level1', function ($e1) {
            $title1 = $e1->variable('title');

            $nested = $e1->withIsolatedContext('level2', function ($e2) {
                return $e2->variable('subtitle');
            }, isIteration: true);

            return "$title1\n$nested";
        }, isIteration: true);

        $expected = <<<'BLADE'
{{ $level1->title }}
{{ $level2->subtitle }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_uses_isolated_iteration_convenience_method()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withIsolatedIteration('item', function ($e) {
            return $e->variable('name');
        });

        $expected = '{{ $item->name }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_combines_isolated_context_with_foreach()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withIsolatedIteration('set', function ($e) {
            return $e->forEach('items', 'set', null, function ($e2) {
                return $e2->variable('title');
            });
        });

        $expected = <<<'BLADE'
@foreach ($set->items as $set)
    {{ $set->title }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_complex_scenario_with_counted_vars_and_isolated_contexts()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('page', isIteration: false);

        $result = $emit->withCountedVariable('set', function ($setVar1) use ($emit) {
            return $emit->forEach('grid', $setVar1, null, function ($e) use ($emit) {
                $content = $e->variable('title');

                $nested = $emit->withCountedVariable('set', function ($setVar2) use ($emit) {
                    return $emit->withIsolatedIteration($setVar2, function ($e2) {
                        return $e2->forEach('rows', 'row', null, function ($e3) {
                            return $e3->variable('value');
                        });
                    });
                });

                return $content."\n".$nested;
            });
        });

        $expected = <<<'BLADE'
@foreach ($page->grid as $set)
    {{ $set->title }}
    @foreach ($set1->rows as $row)
        {{ $row->value }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_isolates_nested_foreach_with_same_variable_names()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withCountedVariable('set', function ($setVar1) use ($emit) {
            return $emit->withIsolatedIteration($setVar1, function ($e1) use ($emit) {
                return $e1->forEach('items', 'item', null, function ($e2) use ($emit) {
                    $title = $e2->variable('title');

                    $nested = $emit->withCountedVariable('set', function ($setVar2) use ($emit) {
                        return $emit->withIsolatedIteration($setVar2, function ($e3) {
                            return $e3->forEach('nested_items', 'nested', null, function ($e4) {
                                return $e4->variable('label');
                            });
                        });
                    });

                    return "$title\n$nested";
                });
            });
        });

        $expected = <<<'BLADE'
@foreach ($set->items as $item)
    {{ $item->title }}
    @foreach ($set1->nested_items as $nested)
        {{ $nested->label }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_restores_stack_even_on_exception_in_isolated_context()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('original', isIteration: true);

        try {
            $emit->withIsolatedContext('isolated', function ($e) {
                throw new \Exception('Test exception');
            }, isIteration: true);
        } catch (\Exception $e) {
        }

        $this->assertEquals('original', $emit->getCurrentRoot());
    }

    #[Test]
    public function it_demonstrates_practical_grid_replicator_scenario()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withCountedVariable('set', function ($setVar1) use ($emit) {
            return $emit->forEach('grid_field', $setVar1, null, function ($e) use ($emit) {
                $gridType = $e->variable('type');

                $replicatorContent = $emit->withCountedVariable('set', function ($setVar2) use ($emit) {
                    return $emit->withIsolatedIteration($setVar2, function ($e2) {
                        return $e2->forEach('replicator_sets', 'repset', null, function ($e3) {
                            return $e3->variable('set_type')->variable('content');
                        });
                    });
                });

                return $gridType."\n".$replicatorContent;
            });
        });

        $expected = <<<'BLADE'
@foreach ($grid_field as $set)
    {{ $set->type }}
    @foreach ($set1->replicator_sets as $repset)
        {{ $repset->set_type }}
        {{ $repset->content }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_shares_variable_counters_across_multiple_emitter_instances()
    {
        BladeSourceEmitter::resetStack();

        $emit1 = new BladeSourceEmitter;
        $emit2 = new BladeSourceEmitter;
        $emit3 = new BladeSourceEmitter;

        $var1 = $emit1->getCountedVariable('set');
        $var2 = $emit2->getCountedVariable('set');
        $var3 = $emit3->getCountedVariable('set');

        $this->assertEquals('set', $var1);
        $this->assertEquals('set1', $var2);
        $this->assertEquals('set2', $var3);

        $this->assertEquals(2, $emit1->getVariableCounter('set'));
        $this->assertEquals(2, $emit2->getVariableCounter('set'));
        $this->assertEquals(2, $emit3->getVariableCounter('set'));
    }

    #[Test]
    public function it_releases_counted_variables_across_instances()
    {
        BladeSourceEmitter::resetStack();

        $emit1 = new BladeSourceEmitter;
        $emit2 = new BladeSourceEmitter;

        $emit1->getCountedVariable('set');
        $emit1->getCountedVariable('set');
        $emit2->getCountedVariable('set');

        $this->assertEquals(2, $emit2->getVariableCounter('set'));

        $emit1->releaseCountedVariable('set');

        $this->assertEquals(1, $emit2->getVariableCounter('set'));
    }

    #[Test]
    public function it_handles_practical_scenario_with_generator_creating_new_emitters()
    {
        BladeSourceEmitter::resetStack();

        $mainEmit = new BladeSourceEmitter;

        $result = $mainEmit->withCountedVariable('set', function ($setVar1) {
            $fieldEmit1 = new BladeSourceEmitter;
            $content1 = $fieldEmit1->forEach('items1', $setVar1, null, function ($e) {
                return $e->variable('title');
            });

            return $fieldEmit1->withCountedVariable('set', function ($setVar2) use ($content1) {
                $fieldEmit2 = new BladeSourceEmitter;

                $content2 = $fieldEmit2->forEach('items2', $setVar2, null, function ($e) {
                    return $e->variable('subtitle');
                });

                return "$content1\n$content2";
            });
        });

        $expected = <<<'BLADE'
@foreach ($items1 as $set)
    {{ $set->title }}
@endforeach
@foreach ($items2 as $set1)
    {{ $set1->subtitle }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_maintains_counters_during_isolated_context_across_instances()
    {
        BladeSourceEmitter::resetStack();

        $emit1 = new BladeSourceEmitter;

        $var1 = $emit1->getCountedVariable('set');
        $this->assertEquals('set', $var1);
        $this->assertEquals(0, $emit1->getVariableCounter('set'));

        $result = $emit1->withIsolatedIteration('item', function ($e) {
            $var2 = $e->getCountedVariable('set');
            $this->assertEquals('set1', $var2);

            return $e->variable('test');
        });

        $this->assertEquals(1, $emit1->getVariableCounter('set'));

        $emit1->releaseCountedVariable('set');
        $this->assertEquals(0, $emit1->getVariableCounter('set'));
    }

    #[Test]
    public function it_handles_with_counted_variable_with_array_of_names()
    {
        $emit = new BladeSourceEmitter;

        $capturedVars = [];

        $result = $emit->withCountedVariable(['var1', 'var2'], function ($var1, $var2) use (&$capturedVars) {
            $capturedVars = [$var1, $var2];

            return 'result';
        });

        $this->assertEquals(['var1', 'var2'], $capturedVars);
        $this->assertEquals('result', $result);
        $this->assertEquals(-1, $emit->getVariableCounter('var1'));
        $this->assertEquals(-1, $emit->getVariableCounter('var2'));
    }

    #[Test]
    public function it_handles_with_counted_variable_with_array_and_increments()
    {
        $emit = new BladeSourceEmitter;

        $second = null;

        $first = $emit->withCountedVariable(['set', 'item'], function ($set, $item) use (&$second, $emit) {
            $second = $emit->withCountedVariable(['set', 'item'], function ($set, $item) {
                return [$set, $item];
            });

            return [$set, $item];
        });

        $this->assertEquals(['set', 'item'], $first);
        $this->assertEquals(['set1', 'item1'], $second);
    }

    #[Test]
    public function it_releases_all_array_variables_even_on_exception()
    {
        $emit = new BladeSourceEmitter;

        try {
            $emit->withCountedVariable(['var1', 'var2', 'var3'], function ($v1, $v2, $v3) {
                throw new \Exception('Test exception');
            });
        } catch (\Exception $e) {
        }

        $this->assertEquals(-1, $emit->getVariableCounter('var1'));
        $this->assertEquals(-1, $emit->getVariableCounter('var2'));
        $this->assertEquals(-1, $emit->getVariableCounter('var3'));
    }

    #[Test]
    public function it_handles_nested_with_counted_variable_with_arrays()
    {
        $emit = new BladeSourceEmitter;

        $vars = [];

        $emit->withCountedVariable(['set', 'item'], function ($set1, $item1) use (&$vars, $emit) {
            $vars[] = [$set1, $item1];

            $emit->withCountedVariable(['set', 'item'], function ($set2, $item2) use (&$vars, $emit) {
                $vars[] = [$set2, $item2];

                $emit->withCountedVariable(['set', 'item'], function ($set3, $item3) use (&$vars) {
                    $vars[] = [$set3, $item3];
                });
            });
        });

        $this->assertEquals([
            ['set', 'item'],
            ['set1', 'item1'],
            ['set2', 'item2'],
        ], $vars);

        $this->assertEquals(-1, $emit->getVariableCounter('set'));
        $this->assertEquals(-1, $emit->getVariableCounter('item'));
    }

    #[Test]
    public function it_uses_array_counted_variables_in_nested_foreach()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withCountedVariable(['outer', 'inner'], function ($outerVar, $innerVar) use ($emit) {
            return $emit->forEach('grid', $outerVar, null, function ($e) use ($emit, $innerVar) {
                return $e->variable('title')->append(
                    $emit->forEach('items', $innerVar, null, function ($e2) {
                        return $e2->variable('name');
                    })
                );
            });
        });

        $expected = <<<'BLADE'
@foreach ($grid as $outer)
    {{ $outer->title }}
    @foreach ($outer->items as $inner)
        {{ $inner->name }}
    @endforeach
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_mixes_single_and_array_counted_variables()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->withCountedVariable('set', function ($set1) use ($emit) {
            return $emit->withCountedVariable(['row', 'col'], function ($row1, $col1) use ($emit, $set1) {
                return $emit->withCountedVariable('set', function ($set2) use ($set1, $row1, $col1) {
                    $vars = [$set1, $row1, $col1, $set2];

                    return implode(', ', $vars);
                });
            });
        });

        $this->assertEquals('set, row, col, set1', $result);
    }

    #[Test]
    public function it_isolates_variables_from_context_stack()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('items', isIteration: true);

        $result = $emit->isolate(fn ($e) => $e
            ->variables('standalone', 'variable')
        );

        $expected = <<<'BLADE'
{{ $standalone }}
{{ $variable }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_isolates_within_nested_contexts()
    {
        $emit = new BladeSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->variable('title')
            ->append($e->isolate(fn ($e2) => $e2
                ->comment('These variables are isolated')
                ->variables('global_var', 'another_global')
            ))
            ->variable('description')
        );

        $expected = <<<'BLADE'
@foreach ($items as $item)
    {{ $item->title }}
    {{-- These variables are isolated --}}
    {{ $global_var }}
    {{ $another_global }}
    {{ $item->description }}
@endforeach
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_restores_context_after_isolate()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('items', isIteration: true);

        $isolated = $emit->isolate(fn ($e) => $e->variable('standalone'));

        $emit->variable('contextual');
        $normal = (string) $emit;

        $this->assertEquals('{{ $standalone }}', StringUtilities::normalizeLineEndings($isolated));
        $this->assertEquals('{{ $items->contextual }}', StringUtilities::normalizeLineEndings($normal));
    }

    #[Test]
    public function it_handles_nested_isolate_calls()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('outer', isIteration: true);

        $result = $emit->isolate(fn ($e) => $e
            ->variable('first')
            ->append($e->isolate(fn ($e2) => $e2
                ->variable('second')
            ))
        );

        $expected = <<<'BLADE'
{{ $first }}
{{ $second }}
BLADE;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_isolates_with_dot_notation_properties()
    {
        $emit = new BladeSourceEmitter;

        $emit->pushContext('items', isIteration: true);

        $result = $emit->isolate(fn ($e) => $e
            ->variable('config.theme.name')
        );

        $expected = '{{ $config->theme->name }}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function nested_emitters_inherit_configuration()
    {
        $emit = new BladeSourceEmitter();
        $emit->setIndentSize(2);
        $emit->setIndentType('tab');
        $emit->setFinalNewline(true);
        $emit->setPreferComponentSyntax(false);

        $emit->isolate(function ($e) {
            $this->assertEquals(2, $e->getIndentSize());
            $this->assertEquals("\t", $e->getIndentChar());
            $this->assertTrue($e->getFinalNewline());
            $this->assertFalse($e->getPreferComponentSyntax());
        });
    }

    #[Test]
    public function configuration_is_inherited_in_nested_pairs()
    {
        $emit = new BladeSourceEmitter();
        $emit->setIndentSize(8);
        $emit->setNewline('CRLF');

        $result = $emit->pair('items', function ($e) {
            $this->assertEquals(8, $e->getIndentSize());
            $this->assertEquals("\r\n", $e->getLineEnding());

            return '';
        });

        $this->assertIsString($result);
    }

    #[Test]
    public function configuration_is_inherited_in_isolated_contexts()
    {
        $emit = new BladeSourceEmitter();
        $emit->setIndentSize(3);
        $emit->setFinalNewline(true);

        $result = $emit->withIsolatedContext('item', function ($e) {
            $this->assertEquals(3, $e->getIndentSize());
            $this->assertTrue($e->getFinalNewline());

            return '';
        });

        $this->assertIsString($result);
    }
}
