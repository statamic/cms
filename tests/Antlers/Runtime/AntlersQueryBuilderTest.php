<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Tests\Antlers\Fixtures\Addon\Modifiers\IsBuilder;
use Tests\Antlers\Fixtures\Addon\Tags\VarTestTags as VarTest;
use Tests\Antlers\ParserTestCase;

class AntlersQueryBuilderTest extends ParserTestCase
{
    public function test_query_builder_loops_receive_tag_parameters()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->twice()->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));
        $builder->shouldReceive('orderBy')->twice()->withArgs(function ($field, $direction) {
            return $field == 'title' && $direction == 'desc';
        });

        $data = [
            'data' => $builder,
        ];

        $template = <<<'EOT'
{{ data order_by="title:desc" }}{{ title }}{{ /data }}
EOT;

        $this->assertSame('FooBazBar', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ data order_by="title:desc" as="entries" }}{{ entries }}{{ title }}{{ /entries }}{{ /data }}
EOT;

        $this->assertSame('FooBazBar', $this->renderString($template, $data));
    }

    public function test_query_builder_array_plucking_on_tag_pairs()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));

        $builder->shouldReceive('orderBy')->withArgs(function ($field, $direction) {
            return $field == 'title' && $direction == 'desc';
        });

        $data = [
            'items' => $builder,
            'nested' => [
                'items' => $builder,
                'level_two' => [
                    'level-three' => [
                        ['items' => []],
                        ['items' => $builder],
                        ['items' => []],
                    ],
                ],
            ],
            'pluck_item' => 2,
        ];

        $template = <<<'EOT'
<{{ items.0 }}{{ title }}{{ /items.0 }}>
<{{ items[pluck_item] }}{{ title }}{{ /items[pluck_item] }}>
<{{ nested:items.0 }}{{ title }}{{ /nested:items.0 }}>
<{{ nested:items[pluck_item] }}{{ title }}{{ /nested:items[pluck_item] }}>
{{ nested.level_two:level-three.1.items order_by="title:desc" }}<{{ title }}>{{ /nested.level_two:level-three.1.items }}
EOT;

        $expected = <<<'EOT'
<Foo>
<Bar>
<Foo>
<Bar>
<Foo><Baz><Bar>
EOT;

        $this->assertSame($expected, $this->renderString($template, $data));
    }

    public function test_query_builder_loops_receive_tag_parameters_and_can_be_scoped()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->times(3)->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));
        $builder->shouldReceive('orderBy')->times(3)->withArgs(function ($field, $direction) {
            return $field == 'title' && $direction == 'desc';
        });

        $data = [
            'block' => [
                'taxonomies' => $builder,
            ],
        ];

        $template = <<<'EOT'
{{ block:taxonomies order_by="title:desc" }}{{ title }}{{ /block:taxonomies }}
EOT;

        $this->assertSame('FooBazBar', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ block:taxonomies order_by="title:desc" as="entries" }}{{ entries }}{{ title }}{{ /entries }}{{ /block:taxonomies }}
EOT;

        $this->assertSame('FooBazBar', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ scope:the_scope }}
{{ the_scope:block:taxonomies order_by="title:desc" as="entries" }}{{ entries }}{{ title }}{{ /entries }}{{ /the_scope:block:taxonomies }}
{{ /scope:the_scope }}
EOT;

        $this->assertSame('FooBazBar', trim($this->renderString($template, $data, true)));
    }

    public function test_strict_variable_query_builders_are_correctly_handled()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->twice()->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));
        $builder->shouldReceive('orderBy')->twice()->withArgs(function ($field, $direction) {
            return $field == 'title' && $direction == 'desc';
        });

        $data = [
            'data' => $builder,
        ];

        $template = <<<'EOT'
{{ $data order_by="title:desc" }}{{ title }}{{ /$data }}
EOT;

        $this->assertSame('FooBazBar', $this->renderString($template, $data));

        $template = <<<'EOT'
{{ $data order_by="title:desc" as="entries" }}{{ entries }}{{ title }}{{ /entries }}{{ /$data }}
EOT;

        $this->assertSame('FooBazBar', $this->renderString($template, $data));
    }

    public function test_query_builders_can_be_used_like_variables()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));

        $data = [
            'data' => $builder,
        ];

        $template = <<<'EOT'
{{ data | pluck:title | sentence_list }}
EOT;

        $this->assertSame('Foo, Baz, and Bar', $this->renderString($template, $data, true));
    }

    public function test_query_builders_do_not_leak_to_the_next_node()
    {
        (new class extends Tags
        {
            public static $handle = 'thetag';

            public function index()
            {
                $src = $this->params->get('src');

                return $src;
            }
        })::register();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));

        $data = [
            'data' => $builder,
        ];

        // If the builder leaks, the runtime will try and call "query:thetag" for the next node.
        $template = <<<'EOT'
<{{ data | pluck:title | sentence_list }}><{{ thetag src="source" }}>
EOT;

        $this->assertSame('<Foo, Baz, and Bar><source>', $this->renderString($template, $data, true));
    }

    public function test_query_builders_can_materialize_within_the_sandbox()
    {
        $clientData = [
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ];

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->times(4)->andReturn(collect($clientData));

        $data = [
            'clients' => $builder,
            'nested' => [
                'clients' => $builder,
            ],
        ];

        VarTest::register();

        $template = <<<'EOT'
{{ var_test :variable="arr('clients' => clients)" }}
EOT;

        $this->renderString($template, $data, true);

        $this->assertSame(['clients' => $clientData], VarTest::$var);

        $template = <<<'EOT'
{{ var_test :variable="clients" }}
EOT;

        $this->renderString($template, $data, true);
        $this->assertSame(VarTest::$var, $builder);

        $this->renderString('{{ var_test :variable="clients.0.title" }}', $data, true);
        $this->assertSame('Foo', VarTest::$var);

        $this->renderString('{{ var_test :variable="clients.1.title" }}', $data, true);
        $this->assertSame('Baz', VarTest::$var);

        $this->renderString('{{ var_test :variable="clients.2.title" }}', $data, true);
        $this->assertSame('Bar', VarTest::$var);

        $this->renderString('{{ var_test :variable="nested.clients" }}', $data, true);
        $this->assertSame(VarTest::$var, $builder);
    }

    public function test_query_builders_are_not_resolved_for_modifiers()
    {
        // If the Environment mis-manages the builder instance
        // the test case should throw an exception stating
        // that the get() does not exist on the mock.
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('count')->once()->andReturn(3);

        $data = [
            'clients' => $builder,
        ];
        $template = <<<'EOT'
{{ clients | length }}
EOT;

        $this->assertSame('3', $this->renderString($template, $data));
    }

    public function test_query_builder_instances_are_preserved()
    {
        IsBuilder::register();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->times(4)->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));
        $builder->shouldReceive('orderBy')->times(1)->withArgs(function ($field, $direction) {
            return $field == 'title' && $direction == 'desc';
        });
        $builder->shouldReceive('orderBy')->times(3)->withArgs(function ($field, $direction) {
            return $field == 'title' && $direction == 'asc';
        });
        $builder->shouldReceive('count')->times(3)->andReturn(3);

        $data = [
            'query_builder_field' => $builder,
        ];

        // Reset the callbacks from any other tests.
        GlobalRuntimeState::$peekCallbacks = [];

        // We will perform some assertions here as well. Since the runtime environment
        // has special handling around modifiers, we can use a peek callback to
        // check what the actual value is set to without touching it at all.
        GlobalRuntimeState::$peekCallbacks[] = function (NodeProcessor $processor) {
            $activeData = $processor->getActiveData();
            $this->assertArrayHasKey('query_builder_field', $activeData);
            $this->assertFalse($activeData['query_builder_field'] instanceof Collection);
        };

        $template = <<<'EOT'
{{ query_builder_field order_by="title:desc" }}
{{ ___internal_debug:peek }}
{{ title }}
{{ query_builder_field | length /}}
{{ query_builder_field | is_builder /}}
{{ query_builder_field order_by="title:asc" }}{{ ___internal_debug:peek }}<{{ title }}>{{ /query_builder_field }}
{{ query_builder_field | is_builder /}}
{{ /query_builder_field }}
EOT;

        $expected = <<<'EOT'
Foo
3
Statamic\Contracts\Query\Builder
<Foo><Baz><Bar>
Statamic\Contracts\Query\Builder


Baz
3
Statamic\Contracts\Query\Builder
<Foo><Baz><Bar>
Statamic\Contracts\Query\Builder


Bar
3
Statamic\Contracts\Query\Builder
<Foo><Baz><Bar>
Statamic\Contracts\Query\Builder
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data)));
    }

    public function test_using_builders_as_a_pair_does_not_mutate_existing_variable()
    {
        IsBuilder::register();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->times(2)->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Baz'],
            ['title' => 'Bar'],
        ]));

        $data = [
            'query_builder_field' => $builder,
        ];

        $template = <<<'EOT'
{{ query_builder_field | is_builder }}
{{ query_builder_field }}<{{ title }}>{{ /query_builder_field }}
{{ query_builder_field | is_builder }}
{{ query_builder_field }}<{{ title }}>{{ /query_builder_field }}
{{ query_builder_field | is_builder }}
EOT;

        $expected = <<<'EOT'
Statamic\Contracts\Query\Builder
<Foo><Baz><Bar>
Statamic\Contracts\Query\Builder
<Foo><Baz><Bar>
Statamic\Contracts\Query\Builder
EOT;

        $this->assertSame($expected, $this->renderString($template, $data));
    }

    public function test_nested_query_builders_process_assignments_correctly()
    {
        $totals = [];
        GlobalRuntimeState::$peekCallbacks = [];
        GlobalRuntimeState::$peekCallbacks[] = function (NodeProcessor $processor) use (&$totals) {
            $data = $processor->getActiveData();
            $totals[] = intval($data['total_time']);
        };

        $builderOne = Mockery::mock(Builder::class);
        $builderTwo = Mockery::mock(Builder::class);

        $builderTwo->shouldReceive('get')->andReturn(collect([
            ['title' => 'Builder-2 One', 'duration' => 10],
            ['title' => 'Builder-2 Two', 'duration' => 10],
        ]));

        $builderOne->shouldReceive('get')->andReturn(collect([
            ['title' => 'Builder-1 One', 'nested_builder' => $builderTwo],
            ['title' => 'Builder-1 Two', 'nested_builder' => $builderTwo],
            ['title' => 'Builder-1 Three', 'nested_builder' => $builderTwo],
        ]));

        $data = [
            'items' => $builderOne,
        ];

        $template = <<<'EOT'
{{ total_time = 0 }}
{{ items }}
    {{ nested_builder }}
        {{ total_time += duration }}
        {{ ___internal_debug:peek }}
    {{ /nested_builder }}
{{ /items }}
Total: {{ total_time }}
EOT;

        $this->assertSame('Total: 60', trim($this->renderString($template, $data)));
        $this->assertSame([10, 20, 30, 40, 50, 60], $totals);
    }
}
