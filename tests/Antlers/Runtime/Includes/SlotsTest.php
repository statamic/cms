<?php

namespace Tests\Antlers\Runtime\Includes;

use Illuminate\Support\Facades\Blade;
use Statamic\Tags\Tags;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class SlotsTest extends ParserTestCase
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

    private $spy;

    private function registerSpyTag(): void
    {
        $this->spy = new class extends Tags
        {
            public static $handle = 'spy';

            public static $count = 0;

            public function index()
            {
                self::$count++;

                return '';
            }
        };

        $this->spy::$count = 0;
        $this->spy::register();
    }

    public function test_default_slot()
    {
        $this->viewShouldReturnRaw('wrapper', '<div>{{ slot }}</div>');

        $this->assertSame('<div>Body</div>', $this->render('{{ include:wrapper }}Body{{ /include:wrapper }}'));
    }

    public function test_a_default_slot_may_be_passed_inline_as_a_param()
    {
        $this->viewShouldReturnRaw('greeting', '<div>{{ slot }}</div>');

        $this->assertSame('<div>Hello</div>', $this->render('{{ include:greeting slot="Hello" }}'));
    }

    public function test_if_slot_is_false_when_no_body_is_given()
    {
        $this->viewShouldReturnRaw('wrapper', '{{ if slot }}HAS{{ else }}NONE{{ /if }}');

        $this->assertSame('NONE', $this->render('{{ include:wrapper }}{{ /include:wrapper }}'));
        $this->assertSame('NONE', $this->render('{{ include:wrapper }}'));
        $this->assertSame('NONE', $this->render('{{ include:wrapper }}   {{ /include:wrapper }}'));
    }

    public function test_default_slot_presence_does_not_bleed_between_includes()
    {
        $this->viewShouldReturnRaw('wrapper', '{{ if slot }}HAS{{ else }}NONE{{ /if }}');

        $this->assertSame(
            'HAS|NONE',
            $this->render('{{ include:wrapper }}body{{ /include:wrapper }}|{{ include:wrapper }}{{ /include:wrapper }}')
        );
    }

    public function test_slot_sees_outer_scope_but_the_view_does_not()
    {
        $this->viewShouldReturnRaw('wrapper', '<view>{{ title }}</view><slot>{{ slot }}</slot>');

        $template = '{{ include:wrapper }}{{ title }}{{ /include:wrapper }}';

        $this->assertSame('<view></view><slot>Caller</slot>', $this->render($template, ['title' => 'Caller']));
    }

    public function test_slot_content_with_text_around_a_pair_is_preserved()
    {
        $this->viewShouldReturnRaw('wrapper', '<w>{{ slot }}</w>');

        $tpl = '{{ include:wrapper }}before{{ if show }}mid{{ /if }}after{{ /include:wrapper }}';

        $this->assertSame('<w>beforemidafter</w>', $this->render($tpl, ['show' => true]));
    }

    public function test_a_slot_containing_only_a_pair_is_considered_present()
    {
        $this->viewShouldReturnRaw('wrapper', '{{ if slot }}HAS[{{ slot }}]{{ else }}NONE{{ /if }}');

        $tpl = '{{ include:wrapper }}{{ if show }}X{{ /if }}{{ /include:wrapper }}';

        $this->assertSame('HAS[X]', $this->render($tpl, ['show' => true]));
        $this->assertSame('HAS[]', $this->render($tpl, ['show' => false]));
    }

    public function test_slot_content_can_access_include_params()
    {
        $this->viewShouldReturnRaw('wrapper', '<div>{{ slot }}</div>');

        $template = '{{ include:wrapper :params="data" handle_prefix="card_" foo="named" }}{{ params:foo }}|{{ params:title }}{{ /include:wrapper }}';

        $this->assertSame('<div>named|Title</div>', $this->render($template, [
            'data' => ['foo' => 'spread', 'card_title' => 'Title'],
        ]));
    }

    public function test_named_slots()
    {
        $this->viewShouldReturnRaw('card', '<h>{{ slot:header }}</h><b>{{ slot }}</b>');

        $template = '{{ include:card }}{{ slot:header }}Title{{ /slot:header }}Body{{ /include:card }}';

        $this->assertSame('<h>Title</h><b>Body</b>', $this->render($template));
    }

    public function test_named_slots_do_not_replace_same_named_params()
    {
        $this->viewShouldReturnRaw('card', '[{{ header }}][{{ slot:header }}][{{ params:header }}]');
        $this->viewShouldReturnRaw('card_b', '[{{ $header }}][<s:slot:header />][{{ $params[\'header\'] }}]', 'blade.php');

        $antlers = '{{ include:card header="Data" }}{{ slot:header }}Slot{{ /slot:header }}{{ /include:card }}';
        $blade = '<s:include:card_b header="Data"><s:slot:header>Slot</s:slot:header></s:include:card_b>';

        $this->assertSame('[Data][Slot][Data]', $this->render($antlers));
        $this->assertSame('[Data][Slot][Data]', Blade::render($blade));
    }

    public function test_a_named_slot_falls_back_to_the_views_default_when_not_provided()
    {
        $this->viewShouldReturnRaw('card', '{{ if slot:header }}{{ slot:header }}{{ else }}Default{{ /if }}');

        $this->assertSame('Default', $this->render('{{ include:card }}Body{{ /include:card }}'));
        $this->assertSame('Provided', $this->render('{{ include:card }}{{ slot:header }}Provided{{ /slot:header }}{{ /include:card }}'));
    }

    public function test_named_slot_presence_is_false_when_empty()
    {
        $this->viewShouldReturnRaw('card', '{{ if slot:header }}YES{{ else }}NO{{ /if }}');

        $this->assertSame('NO', $this->render('{{ include:card }}{{ slot:header }}   {{ /slot:header }}{{ /include:card }}'));
    }

    public function test_a_missing_named_slot_used_as_a_pair_renders_nothing()
    {
        $this->viewShouldReturnRaw('card', '<h>{{ slot:header }}fallback{{ /slot:header }}</h>');

        $this->assertSame('<h></h>', $this->render('{{ include:card }}body{{ /include:card }}'));
    }

    public function test_forwarding_a_slot_to_a_nested_include_does_not_clobber_the_outer_one()
    {
        $this->viewShouldReturnRaw('outer', '{{ include:inner tag="I" :__statamic_include_slot_body="slot:body" }}|{{ slot:body }}');
        $this->viewShouldReturnRaw('inner', '{{ slot:body }}');

        $this->assertSame(
            '[I]|[O]',
            $this->render('{{ include:outer tag="O" }}{{ slot:body }}[{{ params:tag }}]{{ /slot:body }}{{ /include:outer }}')
        );
    }

    public function test_pipe_modifiers_can_be_applied_to_slots()
    {
        $this->viewShouldReturnRaw('wrapper', '<{{ slot | upper }}>');
        $this->viewShouldReturnRaw('card', '<{{ slot:header | upper }}>');
        $this->viewShouldReturnRaw('chained', '<{{ slot:header | reverse | upper }}>');

        $this->assertSame('<BODY>', $this->render('{{ include:wrapper }}body{{ /include:wrapper }}'));
        $this->assertSame('<HEAD>', $this->render('{{ include:card }}{{ slot:header }}head{{ /slot:header }}{{ /include:card }}'));
        $this->assertSame('<DAEH>', $this->render('{{ include:chained }}{{ slot:header }}head{{ /slot:header }}{{ /include:chained }}'));
    }

    public function test_default_slot_params_are_passed_as_props()
    {
        $this->viewShouldReturnRaw('scoped', '<{{ slot :label="title" }}>');
        $this->viewShouldReturnRaw('wrapper', '<{{ slot ensure_right="!" }}>');

        $this->assertSame('<[T]>', $this->render('{{ include:scoped title="T" }}[{{ label }}]{{ /include:scoped }}'));
        $this->assertSame('<body[!]>', $this->render('{{ include:wrapper }}body[{{ ensure_right }}]{{ /include:wrapper }}'));
    }

    public function test_named_slot_params_are_always_passed_as_props()
    {
        $this->viewShouldReturnRaw('row', '{{ slot:row :label="title" ensure_right="!" }}');
        $this->viewShouldReturnRaw('card', '{{ slot:head :title="heading" }}');

        $this->assertSame('[t|!]', $this->render('{{ include:row title="t" }}{{ slot:row }}[{{ label }}|{{ ensure_right }}]{{ /slot:row }}{{ /include:row }}'));
        $this->assertSame('[H]', $this->render('{{ include:card heading="H" }}{{ slot:head }}[{{ title }}]{{ /slot:head }}{{ /include:card }}'));
    }

    public function test_scoped_slot_exposes_multiple_props()
    {
        $this->viewShouldReturnRaw('row', '{{ slot:row :label="title" :n="num" }}');

        $template = '{{ include:row title="T" num="3" }}{{ slot:row }}[{{ label }}|{{ n }}]{{ /slot:row }}{{ /include:row }}';

        $this->assertSame('[T|3]', $this->render($template));
    }

    public function test_a_scoped_slot_is_rendered_for_each_iteration_of_a_loop()
    {
        $this->viewShouldReturnRaw('list', '{{ rows }}<{{ slot:row :label="value" :i="count" }}>{{ /rows }}');

        $template = '{{ include:list :rows="data" }}{{ slot:row }}{{ label }}#{{ i }}{{ /slot:row }}{{ /include:list }}';

        $this->assertSame('<a#1><b#2>', $this->render($template, ['data' => [['value' => 'a'], ['value' => 'b']]]));
    }

    public function test_scoped_slot_props_combine_with_caller_scope()
    {
        $this->viewShouldReturnRaw('combo', '{{ slot:item :label="heading" }}');

        $template = '{{ include:combo heading="VIEW" }}{{ slot:item }}[{{ label }}|{{ outer }}]{{ /slot:item }}{{ /include:combo }}';

        $this->assertSame('[VIEW|OUT]', $this->render($template, ['outer' => 'OUT']));
    }

    public function test_scoped_slot_props_override_caller_variables()
    {
        $this->viewShouldReturnRaw('clash', '{{ slot:item :name="inner" }}');

        $template = '{{ include:clash inner="FROM-VIEW" }}{{ slot:item }}[{{ name }}]{{ /slot:item }}{{ /include:clash }}';

        $this->assertSame('[FROM-VIEW]', $this->render($template, ['name' => 'FROM-CALLER']));
    }

    public function test_unused_slots_are_not_rendered()
    {
        $this->registerSpyTag();
        $this->viewShouldReturnRaw('wrapper', 'no slot output');

        $template = '{{ include:wrapper }}{{ spy }}{{ /include:wrapper }}';

        $this->assertSame('no slot output', $this->render($template));
        $this->assertSame(0, $this->spy::$count);
    }

    public function test_a_slot_is_rendered_each_time_it_is_output()
    {
        $this->registerSpyTag();
        $this->viewShouldReturnRaw('wrapper', '{{ slot }}{{ slot }}');

        $template = '{{ include:wrapper }}{{ spy }}{{ /include:wrapper }}';

        $this->render($template);

        $this->assertSame(2, $this->spy::$count);
    }

    public function test_a_condition_checks_slot_presence_without_rendering_it()
    {
        $this->registerSpyTag();
        $this->viewShouldReturnRaw('wrapper', '{{ if slot }}HAS{{ else }}NONE{{ /if }}');

        $this->assertSame('HAS', $this->render('{{ include:wrapper }}{{ spy }}{{ /include:wrapper }}'));
        $this->assertSame(0, $this->spy::$count);
    }

    public function test_a_scoped_slot_guarded_by_a_condition_renders_only_once_with_its_props()
    {
        $this->registerSpyTag();
        $this->viewShouldReturnRaw('list', '{{ if slot:row }}{{ slot:row :label="heading" }}{{ /if }}');

        $template = '{{ include:list heading="H" }}{{ slot:row }}{{ spy }}[{{ label }}]{{ /slot:row }}{{ /include:list }}';

        $this->assertSame('[H]', $this->render($template));
        $this->assertSame(1, $this->spy::$count);
    }

    public function test_antlers_slots_can_be_rendered_by_blade_views()
    {
        $this->viewShouldReturnRaw('list', '{{ $title }}@foreach($rows as $row)<s:slot:item :label="$row" />@endforeach', 'blade.php');

        $template = '{{ include:list :rows="rows" }}{{ slot:title }}<b>Title</b>{{ /slot:title }}{{ slot:item }}[{{ label }}]{{ /slot:item }}{{ /include:list }}';

        $this->assertSame('<b>Title</b>[A][B]', $this->render($template, ['rows' => ['A', 'B']]));
    }

    public function test_blade_slots_can_be_rendered_by_antlers_views()
    {
        $this->viewShouldReturnRaw('list', '{{ rows }}{{ slot:item :label="value" }}{{ /rows }}[{{ params:item }}]');

        $template = '<s:include:list :rows="$rows" title="T"><s:slot:item>[{{ $label }}{{ $params[\'title\'] }}]</s:slot:item></s:include:list>';

        $this->assertSame('[AT][BT][]', Blade::render($template, [
            'rows' => [['value' => 'A'], ['value' => 'B']],
        ]));
    }
}
