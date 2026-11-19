<?php

namespace Tests\View\Scaffolding;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter;
use Tests\TestCase;

class AntlersSourceEmitterTest extends TestCase
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
    public function it_emits_a_variable()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->variable('title');

        $this->assertEquals('{{ title /}}', (string) $result);
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

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_a_pair()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->pair('users', fn ($e) => $e
            ->variables('name', 'email')
        );

        $expected = <<<'ANTLERS'
{{ users }}
    {{ name /}}
    {{ email /}}
{{ /users }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
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
            ['condition' => 'errors', 'template' => '{{ message /}}'],
            ['condition' => 'success', 'template' => '{{ success /}}'],
        ];

        $result = $emit->condition($branches);

        $expected = <<<'ANTLERS'
{{ if errors }}
    {{ message /}}
{{ elseif success }}
    {{ success /}}
{{ /if }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_manages_variable_context_stack()
    {
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

        $result = $emit->variable('title');
        $this->assertEquals('{{ title /}}', (string) $result);

        $emit = new AntlersSourceEmitter;
        $emit->pushContext('item', isIteration: true);
        $result = $emit->variable('title');
        $this->assertEquals('{{ item:title /}}', (string) $result);
    }

    #[Test]
    public function it_handles_nested_contexts()
    {
        $emit = new AntlersSourceEmitter;

        $emit->pushContext('items', isIteration: true);
        $emit->pushContext('meta', isIteration: false);

        $result = $emit->variable('author');
        $this->assertEquals('{{ items:meta:author /}}', (string) $result);

        $emit->popContext();
        $result = $emit->variable('title');
        $this->assertEquals('{{ items:title /}}', (string) $result);
    }

    #[Test]
    public function it_builds_colon_notation_paths()
    {
        $emit = new AntlersSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->variable('meta:author:name');
        $this->assertEquals('{{ item:meta:author:name /}}', (string) $result);
    }

    #[Test]
    public function it_emits_foreach_with_automatic_context()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->variable('title', newLine: true)
            ->variable('description', newLine: true)
        );

        $expected = <<<'ANTLERS'
{{ foreach:items as="item" }}
    {{ item:title /}}
    {{ item:description /}}
{{ /foreach:items }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_nested_foreach_with_contexts()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->forEach('grid', 'row', null, fn ($e) => $e
            ->append($e->forEach('items', 'item', null, fn ($e2) => $e2
                ->variable('name', newLine: true)
            ))
        );

        $expected = <<<'ANTLERS'
{{ foreach:grid as="row" }}
    {{ foreach:row:items as="item" }}
        {{ item:name /}}
    {{ /foreach:row:items }}
{{ /foreach:grid }}
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
    public function it_outputs_properties_with_context_awareness()
    {
        $emit = new AntlersSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->properties('asset', ['url', 'width', 'height']);

        $expected = <<<'ANTLERS'
{{ item:asset:url /}}
{{ item:asset:width /}}
{{ item:asset:height /}}
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
    public function it_outputs_keys_with_context_awareness()
    {
        $emit = new AntlersSourceEmitter;
        $emit->pushContext('item', isIteration: true);

        $result = $emit->keys('settings', ['color', 'size']);

        $expected = <<<'ANTLERS'
{{ item:settings['color'] /}}
{{ item:settings['size'] /}}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, (string) $result);
    }

    #[Test]
    public function it_handles_nested_with_context_calls()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->variable('title')
        );

        $expected = '{{ root1:title /}}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_double_nested_with_context()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->withContext('root1', fn ($e) => $e
            ->append($e->withContext('root2', fn ($e2) => $e2
                ->variable('final')
            ))
        );

        $expected = '{{ root1:root2:final /}}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_generates_first_counted_variable_without_suffix()
    {
        $emit = new AntlersSourceEmitter;

        $var = $emit->getCountedVariable('set');

        $this->assertEquals('set', $var);
        $this->assertEquals(0, $emit->getVariableCounter('set'));
    }

    #[Test]
    public function it_generates_counted_variables_with_incrementing_suffixes()
    {
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

        $emit->getCountedVariable('set');
        $emit->getCountedVariable('set');
        $emit->getCountedVariable('set');

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
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

        $emit->getCountedVariable('set');
        $emit->getCountedVariable('set');
        $emit->getCountedVariable('item');

        $this->assertEquals(1, $emit->getVariableCounter('set'));
        $this->assertEquals(0, $emit->getVariableCounter('item'));

        AntlersSourceEmitter::resetStack();

        $this->assertEquals(-1, $emit->getVariableCounter('set'));
        $this->assertEquals(-1, $emit->getVariableCounter('item'));
    }

    #[Test]
    public function it_gets_all_variable_counters()
    {
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

        $result = $emit->withCountedVariable('set', function ($setVar1) use ($emit) {
            return $emit->forEach('grid', $setVar1, null, function ($e) use ($emit) {
                return $e->variable('title', newLine: true)
                    ->newline()
                    ->append(
                        $emit->withCountedVariable('set', function ($setVar2) use ($emit) {
                            return $emit->forEach('items', $setVar2, null, function ($e2) {
                                return $e2->variable('name', newLine: true);
                            });
                        })
                    );
            });
        });

        $expected = <<<'ANTLERS'
{{ foreach:grid as="set" }}
    {{ set:title /}}
    {{ foreach:set:items as="set1" }}
        {{ set1:name /}}
    {{ /foreach:set:items }}
{{ /foreach:grid }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_creates_isolated_context_with_fresh_variable_stack()
    {
        $emit = new AntlersSourceEmitter;

        $emit->pushContext('outer', isIteration: true);

        $result = $emit->withIsolatedContext('inner', function ($e) {
            return $e->variable('title');
        }, isIteration: true);

        $expected = '{{ inner:title /}}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);

        $this->assertEquals('outer', $emit->getCurrentRoot());
    }

    #[Test]
    public function it_restores_stack_after_isolated_context()
    {
        $emit = new AntlersSourceEmitter;

        $emit->pushContext('original', isIteration: true);
        $this->assertEquals('original', $emit->getCurrentRoot());

        $emit->withIsolatedContext('isolated', function ($e) {
            $this->assertEquals('isolated', $e->getCurrentRoot());

            return $e->variable('test');
        }, isIteration: true);

        $this->assertEquals('original', $emit->getCurrentRoot());
    }

    #[Test]
    public function it_uses_isolated_iteration_convenience_method()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->withIsolatedIteration('item', function ($e) {
            return $e->variable('name');
        });

        $expected = '{{ item:name /}}';

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_combines_isolated_context_with_foreach()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->withIsolatedIteration('set', function ($e) {
            return $e->forEach('items', 'set', null, function ($e2) {
                return $e2->variable('title', newLine: true);
            });
        });

        $expected = <<<'ANTLERS'
{{ foreach:set:items as="set" }}
    {{ set:title /}}
{{ /foreach:set:items }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_handles_with_counted_variable_with_array_of_names()
    {
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

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
        $emit = new AntlersSourceEmitter;

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
    public function it_emits_html_method()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->html('content');

        $this->assertEquals('{{ content /}}', (string) $result);
    }

    #[Test]
    public function it_emits_component_syntax()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->component('users', fn ($e) => $e
            ->variable('name'), ['limit' => '10']);

        $expected = <<<'ANTLERS'
<s:users limit="10">
    {{ name /}}
</s:users>
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_pair_with_context()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->forEach('items', 'item', null, fn ($e) => $e
            ->append($e->pairWithContext('author', fn ($e2) => $e2
                ->variable('name', newLine: true)
                ->variable('email', newLine: true)
            ))
        );

        $expected = <<<'ANTLERS'
{{ foreach:items as="item" }}
    {{ author }}
        {{ author:name /}}
        {{ author:email /}}
    {{ /author }}
{{ /foreach:items }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_shares_variable_counters_across_multiple_emitter_instances()
    {
        AntlersSourceEmitter::resetStack();

        $emit1 = new AntlersSourceEmitter;
        $emit2 = new AntlersSourceEmitter;
        $emit3 = new AntlersSourceEmitter;

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
    public function it_emits_tag_with_parameters()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->tag('collection', fn ($e) => $e
            ->variable('title', newLine: true)
            ->variable('url', newLine: true), ['from' => 'articles', 'limit' => '5']);

        $expected = <<<'ANTLERS'
{{ collection from="articles" limit="5" }}
    {{ title /}}
    {{ url /}}
{{ /collection }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function it_emits_foreach_with_key_value_syntax()
    {
        $emit = new AntlersSourceEmitter;

        $result = $emit->forEach('song_reviews', 'rating', 'song', fn ($e) => $e
            ->variable('song', newLine: true)
            ->variable('rating', newLine: true)
        );

        $expected = <<<'ANTLERS'
{{ foreach:song_reviews as="song|rating" }}
    {{ rating:song /}}
    {{ rating:rating /}}
{{ /foreach:song_reviews }}
ANTLERS;

        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings($result);

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function nested_emitters_inherit_configuration()
    {
        $emit = new AntlersSourceEmitter();
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
        $emit = new AntlersSourceEmitter();
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
        $emit = new AntlersSourceEmitter();
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
