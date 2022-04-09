<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Tests\Antlers\Fixtures\Addon\Modifiers\IsBuilder;
use Tests\Antlers\Fixtures\Addon\Tags\VarTest;
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
        $builder->shouldReceive('get')->once()->andReturn(collect($clientData));

        $data = [
            'clients' => $builder,
        ];

        VarTest::register();

        $template = <<<'EOT'
{{ var_test :variable="arr('clients' => clients)" }}
EOT;

        $this->renderString($template, $data, true);

        $this->assertSame(['clients' => $clientData], VarTest::$var);
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

        // We will perform some assertions here as well. Since the runtime environment
        // has special handling around modifiers, we can use a peek callback to
        // check what the actual value is set to without touching it at all.
        GlobalRuntimeState::$peekCallbacks[] = function(NodeProcessor $processor) {
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
}
