<?php

namespace Tests\Antlers\Runtime\Includes;

use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Fields\Value;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class InteropTest extends ParserTestCase
{
    use FakesViews;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withFakeViews();
    }

    private function render($template, $data = [])
    {
        return $this->renderString($template, $data, true);
    }

    private function tree(): array
    {
        return [
            ['title' => 'A', 'children' => [
                ['title' => 'A1', 'children' => []],
                ['title' => 'A2', 'children' => [['title' => 'A2a', 'children' => []]]],
            ]],
            ['title' => 'B', 'children' => []],
        ];
    }

    public function test_a_slot_can_be_forwarded_into_a_nested_include()
    {
        $this->viewShouldReturnRaw('outer', 'O{{ include:inner }}{{ slot }}{{ /include:inner }}');
        $this->viewShouldReturnRaw('as_param', 'O{{ include:inner :slot="slot" }}');
        $this->viewShouldReturnRaw('inner', 'I<{{ slot }}>');

        $this->assertSame('OI<BODY>', $this->render('{{ include:outer }}BODY{{ /include:outer }}'));
        $this->assertSame('OI<BODY>', $this->render('{{ include:as_param }}BODY{{ /include:as_param }}'));
    }

    public function test_a_slot_can_be_forwarded_through_several_levels()
    {
        $this->viewShouldReturnRaw('l1', '1{{ include:l2 }}<{{ slot }}>{{ /include:l2 }}');
        $this->viewShouldReturnRaw('l2', '2{{ include:l3 }}[{{ slot }}]{{ /include:l3 }}');
        $this->viewShouldReturnRaw('l3', '3({{ slot }})');

        $this->assertSame('123([<TOP>])', $this->render('{{ include:l1 }}TOP{{ /include:l1 }}'));
    }

    public function test_a_slot_may_be_passed_to_another_include_under_a_different_name()
    {
        $this->viewShouldReturnRaw('outer', 'O{{ include:target :x="slot" }}');
        $this->viewShouldReturnRaw('target', 'T<{{ x }}>[{{ params:x }}]');

        $this->assertSame('OT<BODY>[BODY]', $this->render('{{ include:outer }}BODY{{ /include:outer }}'));
    }

    public function test_stacks_can_be_pushed_to_from_a_view_and_from_slot_contents()
    {
        $this->viewShouldReturnRaw('pusher', '{{ push:s }}A{{ /push:s }}X');
        $this->viewShouldReturnRaw('prepender', '{{ prepend:s }}B{{ /prepend:s }}Y');
        $this->viewShouldReturnRaw('wrapper', 'W<s>{{ slot }}</s>');

        $this->assertSame('BA|XY', $this->render('{{ stack:s }}|{{ include:pusher }}{{ include:prepender }}'));
        $this->assertSame(
            'P|W<s>SLOT</s>',
            $this->render('{{ stack:s }}|{{ include:wrapper }}{{ push:s }}P{{ /push:s }}SLOT{{ /include:wrapper }}')
        );
    }

    public function test_sections_can_be_defined_in_a_view_and_in_slot_contents()
    {
        $this->viewShouldReturnRaw('definer', '{{ section:s }}FROM-VIEW{{ /section:s }}X');
        $this->viewShouldReturnRaw('wrapper', 'W<s>{{ slot }}</s>');

        $this->assertSame('FROM-VIEW|X', $this->render('{{ yield:s }}|{{ include:definer }}'));
        $this->assertSame(
            'FROM-SLOT|W<s>X</s>',
            $this->render('{{ yield:s }}|{{ include:wrapper }}{{ section:s }}FROM-SLOT{{ /section:s }}X{{ /include:wrapper }}')
        );
    }

    public function test_a_view_can_yield_a_section_the_caller_defined()
    {
        $this->viewShouldReturnRaw('w', 'W[{{ yield:s }}]');

        $this->assertSame('W[OUTER]', $this->render('{{ section:s }}OUTER{{ /section:s }}{{ include:w }}'));
    }

    public function test_once_only_renders_once_across_repeated_includes()
    {
        $this->viewShouldReturnRaw('p', '{{ once }}ONCE{{ /once }}X');
        $this->viewShouldReturnRaw('w', '{{ slot }}{{ slot }}');

        $this->assertSame('ONCEXX', $this->render('{{ include:p }}{{ include:p }}'));
        $this->assertSame('ONCEXX', $this->render('{{ items }}{{ include:p }}{{ /items }}', ['items' => [[], []]]));
        $this->assertSame('OXX', $this->render('{{ include:w }}{{ once }}O{{ /once }}X{{ /include:w }}'));
    }

    public function test_noparse_and_escaped_literals_survive_slot_contents()
    {
        $this->viewShouldReturnRaw('p', '{{ noparse }}{{ title }}{{ /noparse }}|{{ title }}');
        $this->viewShouldReturnRaw('w', 'W<s>{{ slot }}</s>');

        $this->assertSame('{{ title }}|T', $this->render('{{ include:p title="T" }}'));
        $this->assertSame('W<s>{{ x }}</s>', $this->render('{{ include:w }}{{ noparse }}{{ x }}{{ /noparse }}{{ /include:w }}', ['x' => 'X']));
        $this->assertSame('W<s>{{ x }}</s>', $this->render('{{ include:w }}@{{ x }}{{ /include:w }}', ['x' => 'X']));
    }

    public function test_recursive_nodes_work_around_inside_and_within_slots_of_an_include()
    {
        $this->viewShouldReturnRaw('item', '<i>{{ t }}</i>');
        $this->viewShouldReturnRaw('menu', '{{ nav }}[{{ title }}]{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}{{ /nav }}');
        $this->viewShouldReturnRaw('wrapper', 'W<s>{{ slot }}</s>');

        $recursive = '{{ nav }}[{{ title }}]{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}{{ /nav }}';

        $this->assertSame(
            '<i>A</i><ul><i>A1</i><i>A2</i><ul><i>A2a</i></ul></ul><i>B</i>',
            $this->render('{{ nav }}{{ include:item :t="title" }}{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}{{ /nav }}', ['nav' => $this->tree()])
        );

        $this->assertSame(
            '[A]<ul>[A1][A2]<ul>[A2a]</ul></ul>[B]',
            $this->render('{{ include:menu :nav="tree" }}', ['tree' => $this->tree()])
        );

        $this->assertSame(
            'W<s>[A]<ul>[A1][A2]<ul>[A2a]</ul></ul>[B]</s>',
            $this->render('{{ include:wrapper }}'.$recursive.'{{ /include:wrapper }}', ['nav' => $this->tree()])
        );
    }

    public function test_query_builders_can_be_passed_to_an_include_without_leaking()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(collect([['title' => 'Foo'], ['title' => 'Bar']]));
        $builder->shouldReceive('orderBy')->andReturnSelf();

        $this->viewShouldReturnRaw('list', '{{ rows order_by="title:desc" }}<{{ title }}>{{ /rows }}');
        $this->viewShouldReturnRaw('empty', 'E[{{ rows }}]');

        $this->assertSame(
            '<Foo><Bar>E[]',
            $this->render('{{ include:list :rows="data" }}{{ include:empty }}', ['data' => $builder])
        );
    }

    public function test_augmented_values_survive_being_passed_as_parameters()
    {
        $this->viewShouldReturnRaw('p', '[{{ v }}][{{ v | upper }}][{{ params:v }}]');

        $this->assertSame('[hello][HELLO][hello]', $this->render('{{ include:p :v="v" }}', ['v' => new Value('hello')]));
    }

    public function test_handle_prefix_accepts_a_list_of_prefixes()
    {
        $this->viewShouldReturnRaw('hero', '[{{ title }}][{{ body }}]');

        $this->assertSame('[AT][BB]', $this->render('{{ include:hero :params="d" :handle_prefix="pf" }}', [
            'd' => ['a_title' => 'AT', 'b_body' => 'BB'],
            'pf' => ['a_', 'b_'],
        ]));

        $this->viewShouldReturnRaw('both', '[{{ title }}][{{ a_title }}][{{ b_title }}]');

        $this->assertSame('[FIRST][FIRST][SECOND]', $this->render('{{ include:both :params="d" :handle_prefix="pf" }}', [
            'd' => ['a_title' => 'FIRST', 'b_title' => 'SECOND'],
            'pf' => ['a_', 'b_'],
        ]));
    }

    public function test_the_cache_tag_works_around_and_inside_an_include_with_slots()
    {
        $this->viewShouldReturnRaw('w', 'W<s>{{ slot }}</s>');
        $this->viewShouldReturnRaw('cw', '{{ cache }}W<s>{{ slot }}</s>{{ /cache }}');

        $this->assertSame('W<s>BODY</s>', $this->render('{{ cache }}{{ include:w }}BODY{{ /include:w }}{{ /cache }}'));
        $this->assertSame('W<s>BODY</s>', $this->render('{{ include:cw }}BODY{{ /include:cw }}'));
    }

    public function test_slot_contents_do_not_see_the_views_front_matter()
    {
        $this->viewShouldReturnRaw('fm', "---\nk: FM\n---\n[{{ view:k }}]<{{ slot }}>");

        $this->assertSame('[FM]<BODY>', trim($this->render('{{ include:fm }}BODY{{ /include:fm }}')));
        $this->assertSame('[FM]<[]>', trim($this->render('{{ include:fm }}[{{ view:k }}]{{ /include:fm }}')));
    }
}
