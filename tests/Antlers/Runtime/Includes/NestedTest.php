<?php

namespace Tests\Antlers\Runtime\Includes;

use Statamic\Facades\Cascade;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class NestedTest extends ParserTestCase
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

    public function test_three_levels_deep()
    {
        $this->viewShouldReturnRaw('level1', 'L1[{{ include:level2 }}]');
        $this->viewShouldReturnRaw('level2', 'L2[{{ include:level3 }}]');
        $this->viewShouldReturnRaw('level3', 'L3');

        $this->assertSame('L1[L2[L3]]', $this->render('{{ include:level1 }}'));
    }

    public function test_caller_scope_does_not_reach_any_level()
    {
        $this->viewShouldReturnRaw('level1', 'L1[{{ a }}]{{ include:level2 }}');
        $this->viewShouldReturnRaw('level2', 'L2[{{ a }}]');

        $this->assertSame('L1[]L2[]', $this->render('{{ include:level1 }}', ['a' => 'caller']));
    }

    public function test_params_do_not_implicitly_flow_to_deeper_includes()
    {
        $this->viewShouldReturnRaw('level1', 'L1[{{ b }}]{{ include:level2 }}');
        $this->viewShouldReturnRaw('level2', 'L2[{{ b }}]');

        $this->assertSame('L1[x]L2[]', $this->render('{{ include:level1 b="x" }}'));
    }

    public function test_data_can_be_threaded_down_explicitly()
    {
        $this->viewShouldReturnRaw('level1', 'L1[{{ a }}]{{ include:level2 :a="a" }}');
        $this->viewShouldReturnRaw('level2', 'L2[{{ a }}]');

        $this->assertSame('L1[passed]L2[passed]', $this->render('{{ include:level1 a="passed" }}'));
    }

    public function test_same_variable_name_at_each_level_stays_isolated()
    {
        $this->viewShouldReturnRaw('level1', '{{ x = "1" }}{{ x }}{{ include:level2 }}{{ x }}');
        $this->viewShouldReturnRaw('level2', '{{ x = "2" }}{{ x }}');

        $template = '{{ x = "0" }}{{ include:level1 }}{{ x }}';

        $this->assertSame('1210', $this->render($template));
    }

    public function test_params_accessor_reflects_each_levels_own_params()
    {
        $this->viewShouldReturnRaw('level1', 'L1{{ params:p }}{{ include:level2 p="two" }}');
        $this->viewShouldReturnRaw('level2', 'L2{{ params:p }}');

        $this->assertSame('L1oneL2two', $this->render('{{ include:level1 p="one" }}'));
    }

    public function test_outer_slots_do_not_leak_into_a_nested_include()
    {
        $this->viewShouldReturnRaw('outer', '<t>{{ slot:otitle }}</t>{{ include:inner }}{{ slot:ititle }}INNER{{ /slot:ititle }}{{ /include:inner }}');
        $this->viewShouldReturnRaw('inner', '<a>{{ slot:ititle }}</a><b>[{{ slot:otitle }}]</b>');

        $template = '{{ include:outer }}{{ slot:otitle }}OUTER{{ /slot:otitle }}{{ /include:outer }}';

        $this->assertSame('<t>OUTER</t><a>INNER</a><b>[]</b>', $this->render($template));
    }

    public function test_a_slot_with_an_include_still_sees_the_callers_scope()
    {
        $this->viewShouldReturnRaw('wrapper', '<w>{{ slot }}</w>');
        $this->viewShouldReturnRaw('inner', 'I[{{ name }}]');

        $template = '{{ include:wrapper }}{{ include:inner :name="caller_var" }}{{ /include:wrapper }}';

        $this->assertSame('<w>I[CV]</w>', $this->render($template, ['caller_var' => 'CV']));
    }

    public function test_looped_includes_keep_params_and_slots_isolated()
    {
        $this->viewShouldReturnRaw('row', '{{ n }}:{{ slot }}:{{ params:n }};');
        $items = collect()->range(1, 10)->map(fn ($value) => compact('value'))->all();
        $expected = collect()->range(1, 10)->map(fn ($value) => "{$value}:{$value}:{$value};")->implode('');

        $template = '{{ items }}{{ include:row :n="value" }}{{ value }}{{ /include:row }}{{ /items }}';

        $this->assertSame($expected, $this->render($template, ['items' => $items]));
    }

    public function test_scope_is_preserved_through_alternating_view_engines()
    {
        Cascade::set('secret', 'cascade');
        $this->viewShouldReturnRaw('outer', 'O[{{ label }}|{{ secret }}]{{ include:middle :label="label" :rows="rows" }}');
        $this->viewShouldReturnRaw('middle', 'M[{{ $label }}|{{ $secret ?? \'\' }}]<s:include:inner :label="$label" :rows="$rows"><s:slot:item>[{{ $params[\'label\'] }}:{{ $value }}:{{ $secret ?? \'\' }}]</s:slot:item></s:include:inner>', 'blade.php');
        $this->viewShouldReturnRaw('inner', 'I[{{ label }}|{{ secret }}]{{ rows }}{{ slot:item :value="value" }}{{ /rows }}');

        $template = '{{ include:outer label="L" :rows="rows" }}';

        $this->assertSame('O[L|]M[L|]I[L|][L:A:][L:B:]', $this->render($template, [
            'secret' => 'caller',
            'rows' => [['value' => 'A'], ['value' => 'B']],
        ]));
    }

    public function test_recursive_include_with_termination()
    {
        $this->viewShouldReturnRaw('tree', '{{ if depth > 0 }}<n>{{ include:tree :depth="depth|subtract:1" }}</n>{{ /if }}');

        $this->assertSame('<n><n><n></n></n></n>', $this->render('{{ include:tree :depth="3" }}'));
    }

    public function test_an_include_with_its_own_slot_can_live_inside_a_named_slot()
    {
        $this->viewShouldReturnRaw('card', '<h>{{ slot:header }}</h>');
        $this->viewShouldReturnRaw('badge', '<bdg>{{ slot }}</bdg>');

        $template = '{{ include:card }}{{ slot:header }}{{ include:badge }}LBL{{ /include:badge }}{{ /slot:header }}{{ /include:card }}';

        $this->assertSame('<h><bdg>LBL</bdg></h>', $this->render($template));
    }

    public function test_an_assignment_in_slot_content_does_not_leak()
    {
        $this->viewShouldReturnRaw('wrapper', '<w>{{ slot }}</w>[{{ leaked }}]');

        $template = '{{ include:wrapper }}{{ leaked = "fromslot" }}{{ leaked }}{{ /include:wrapper }}|{{ leaked }}';

        $this->assertSame('<w>fromslot</w>[]|', $this->render($template));
    }
}
