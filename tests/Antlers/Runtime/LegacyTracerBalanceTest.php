<?php

namespace Tests\Antlers\Runtime;

use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;
use Stringable;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Tests\View\Instrumentation\Concerns\TracesRenders;
use Tests\View\Instrumentation\Support\RecordingLegacyTracer;

class LegacyTracerBalanceTest extends ParserTestCase
{
    use FakesViews, InteractsWithRuntimeConfigurations, TracesRenders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['statamic.tags']['balance_pair'] = BalancePairTag::class;
    }

    public function test_tags_returning_parsed_content_exit_once_with_their_full_output()
    {
        $tracer = $this->trace(
            '<ul>{{ balance_pair }}<li class="{{ kind }}">{{ title }}</li>{{ /balance_pair }}</ul>',
            ['title' => 'Hello'],
            '<ul><li class="item">Hello</li></ul>'
        );

        $this->assertBalanced($tracer);
        $this->assertSame(['<li class="item">Hello</li>'], $this->exitOutputs($tracer, $this->expression('balance_pair')));
    }

    public function test_tags_returning_parse_loop_content_exit_once_with_their_full_output()
    {
        $tracer = $this->trace(
            '<ul>{{ balance_pair:loop }}<li>{{ kind }}</li>{{ /balance_pair:loop }}</ul>',
            [],
            '<ul><li>first</li><li>second</li></ul>'
        );

        $this->assertBalanced($tracer);
        $this->assertSame(['<li>first</li><li>second</li>'], $this->exitOutputs($tracer, $this->expression('balance_pair:loop')));
    }

    public function test_tags_returning_arrays_exit_once_with_the_rendered_loop()
    {
        $tracer = $this->trace(
            '<ul>{{ balance_pair:rows }}<li>{{ kind }}</li>{{ /balance_pair:rows }}</ul>',
            [],
            '<ul><li>a</li><li>b</li></ul>'
        );

        $this->assertBalanced($tracer);
        $this->assertSame(['<li>a</li><li>b</li>'], $this->exitOutputs($tracer, $this->expression('balance_pair:rows')));
        $this->assertSame(['a', 'b'], $this->exitOutputs($tracer, $this->expression('kind')));
    }

    public function test_recursive_children_exit_once_with_every_child_rendered()
    {
        $tracer = $this->trace(
            '<ul>{{ items }}<li>{{ title }}{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}</li>{{ /items }}</ul>',
            ['items' => [
                ['title' => 'A', 'children' => [['title' => 'A1', 'children' => []], ['title' => 'A2', 'children' => []]]],
                ['title' => 'B', 'children' => []],
            ]],
            '<ul><li>A<ul><li>A1</li><li>A2</li></ul></li><li>B</li></ul>'
        );

        $this->assertBalanced($tracer);
        $this->assertSame(['<li>A1</li><li>A2</li>'], $this->exitOutputs($tracer, fn (AbstractNode $node) => $node instanceof RecursiveNode));
        $this->assertSame(['<li>A<ul><li>A1</li><li>A2</li></ul></li><li>B</li>'], $this->exitOutputs($tracer, $this->expression('items')));
    }

    public function test_variable_prints_exit_once_with_their_value()
    {
        $tracer = $this->trace('<p>{{ title }}</p>', ['title' => 'Hello'], '<p>Hello</p>');

        $this->assertBalanced($tracer);
        $this->assertSame(['Hello'], $this->exitOutputs($tracer, $this->expression('title')));
    }

    public function test_conditions_with_else_branches_exit_once_per_head()
    {
        $tracer = $this->trace('{{ if show }}A{{ else }}B{{ /if }}', ['show' => false], 'B');

        $this->assertBalanced($tracer);
        $this->assertCount(1, $this->exitOutputs($tracer, fn (AbstractNode $node) => $node instanceof ConditionNode));
        $this->assertSame([false], $this->exitOutputs($tracer, $this->expression('if show')));
        $this->assertSame([null], $this->exitOutputs($tracer, $this->expression('else')));
    }

    public function test_partials_exit_once_with_their_rendered_view()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('balance-inner', '<em>{{ title }}</em>');

        $tracer = $this->trace('<p>{{ partial:balance-inner }}</p>', ['title' => 'Hello'], '<p><em>Hello</em></p>');

        $this->assertBalanced($tracer);
        $this->assertSame(['<em>Hello</em>'], $this->exitOutputs($tracer, $this->expression('partial:balance-inner')));
    }

    protected function trace(string $template, array $data, string $expected): RecordingLegacyTracer
    {
        $tracer = new RecordingLegacyTracer;
        $parser = $this->tracingParser($tracer);

        $this->assertSame($expected, (string) $parser->parse($template, $data));
        $this->assertSame(0, $this->runtimeConfiguration($parser)->traceManager->renderDepth());

        return $tracer;
    }

    protected function assertBalanced(RecordingLegacyTracer $tracer): void
    {
        $entered = array_count_values(array_map('spl_object_id', $tracer->entered));
        $exited = array_count_values(array_map(fn (array $exit) => spl_object_id($exit[0]), $tracer->exits));

        ksort($entered);
        ksort($exited);

        $this->assertNotEmpty($entered);
        $this->assertSame($entered, $exited);
        $this->assertGreaterThanOrEqual(1, $tracer->completions);
    }

    protected function exitOutputs(RecordingLegacyTracer $tracer, callable $matches): array
    {
        $outputs = [];

        foreach ($tracer->exits as [$node, $output]) {
            if ($matches($node)) {
                $outputs[] = $output instanceof Stringable ? (string) $output : $output;
            }
        }

        return $outputs;
    }

    protected function expression(string $expression): callable
    {
        return fn (AbstractNode $node) => $node instanceof AntlersNode && trim((string) $node->content) === $expression;
    }
}

class BalancePairTag extends Tags
{
    public function index()
    {
        return $this->parse(['kind' => 'item']);
    }

    public function loop()
    {
        return $this->parseLoop([['kind' => 'first'], ['kind' => 'second']]);
    }

    public function rows()
    {
        return [['kind' => 'a'], ['kind' => 'b']];
    }
}
