<?php

namespace Tests\Antlers\Runtime;

use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Tags\Tags;
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
}
