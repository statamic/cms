<?php

namespace Tests\Antlers\Runtime\Includes;

use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class IncludeTagTest extends ParserTestCase
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

    public function test_it_renders_a_view()
    {
        $this->viewShouldReturnRaw('greeting', 'Hello');

        $this->assertSame('Hello', $this->render('{{ include:greeting }}'));
    }

    public function test_it_renders_a_view_using_the_src_form()
    {
        $this->viewShouldReturnRaw('greeting', 'Hi');

        $this->assertSame('Hi', $this->render('{{ include src="greeting" }}'));
    }

    public function test_an_empty_src_is_rejected()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The include tag requires a view name or the [src] parameter.');

        $this->render('{{ include src="" }}');
    }

    public function test_a_view_named_index_can_still_be_included()
    {
        $this->viewShouldReturnRaw('index', 'IDX');

        $this->assertSame('IDX', $this->render('{{ include:index }}'));
    }

    public function test_params_are_available_as_variables()
    {
        $this->viewShouldReturnRaw('greeting', 'Hello {{ name }}');

        $this->assertSame('Hello World', $this->render('{{ include:greeting name="World" }}'));
    }

    public function test_caller_scope_is_not_captured()
    {
        $this->viewShouldReturnRaw('greeting', '[{{ secret }}]');

        $this->assertSame('[]', $this->render('{{ include:greeting }}', ['secret' => 'leak']));
    }

    public function test_loop_variables_are_not_captured()
    {
        $this->viewShouldReturnRaw('item', '[{{ value }}]');

        $template = '{{ items }}{{ include:item }}{{ /items }}';

        $this->assertSame('[][]', $this->render($template, ['items' => [['value' => 'a'], ['value' => 'b']]]));
    }

    public function test_assignments_inside_an_include_do_not_leak_out()
    {
        $this->viewShouldReturnRaw('assigner', '{{ leaked = "in-include" }}{{ leaked }}');

        $template = '{{ include:assigner }}|{{ leaked }}';

        $this->assertSame('in-include|', $this->render($template));
    }

    public function test_reassigning_a_passed_variable_does_not_change_the_caller()
    {
        $this->viewShouldReturnRaw('reassign', '{{ foo = "changed" }}{{ foo }}');

        $template = '{{ foo = "original" }}{{ foo }}|{{ include:reassign :foo="foo" }}|{{ foo }}';

        $this->assertSame('original|changed|original', $this->render($template));
    }

    public function test_params_array_is_spread_into_the_scope_and_overridden_by_explicit_params()
    {
        $this->viewShouldReturnRaw('card', '<{{ title }}><{{ subtitle }}>');

        $data = ['title' => 'T', 'subtitle' => 'S'];

        $this->assertSame('<T><S>', $this->render('{{ include:card :params="data" }}', ['data' => $data]));
        $this->assertSame('<Override><S>', $this->render('{{ include:card :params="data" title="Override" }}', ['data' => $data]));
    }

    public function test_params_accessor_returns_the_merged_params()
    {
        $this->viewShouldReturnRaw('card', '[{{ params:title }}][{{ params:subtitle }}]');

        $this->assertSame(
            '[Named][S]',
            $this->render('{{ include:card :params="data" title="Named" }}', ['data' => ['title' => 'T', 'subtitle' => 'S']])
        );
    }

    public function test_meta_params_never_appear_as_data()
    {
        $this->viewShouldReturnRaw('card', '[{{ params:src }}][{{ params:when }}][{{ params:handle_prefix }}][{{ params:params }}]');

        $this->assertSame(
            '[][][][]',
            $this->render('{{ include:card :params="data" handle_prefix="x_" }}', ['data' => ['src' => 'sneaky', 'when' => 'sneaky']])
        );
    }

    public function test_params_must_be_an_associative_array()
    {
        $this->viewShouldReturnRaw('card', 'C');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('must be an associative array');

        $this->render('{{ include:card :params="bad" }}', ['bad' => ['a', 'b', 'c']]);
    }

    public function test_reserved_params_cannot_be_spread()
    {
        $this->viewShouldReturnRaw('card', 'Card');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot pass reserved parameter [__frontmatter]');

        $this->render('{{ include:card :params="data" }}', [
            'data' => ['__frontmatter' => 'value'],
        ]);
    }

    public function test_handle_prefix_exposes_spread_values_under_both_names()
    {
        $this->viewShouldReturnRaw('hero', '<{{ title }}><{{ body }}>[{{ hero_title }}][{{ params:hero_title }}][{{ other }}]');

        $template = '{{ include:hero :params="data" handle_prefix="hero_" }}';

        $this->assertSame(
            '<HT><HB>[HT][HT][O]',
            $this->render($template, ['data' => ['hero_title' => 'HT', 'hero_body' => 'HB', 'other' => 'O']])
        );
    }

    public function test_a_prefixed_spread_value_wins_over_a_non_prefixed_one()
    {
        $this->viewShouldReturnRaw('hero', '<{{ title }}>');

        $template = '{{ include:hero :params="data" handle_prefix="hero_" }}';

        $this->assertSame(
            '<PREFIXED>',
            $this->render($template, ['data' => ['hero_title' => 'PREFIXED', 'title' => 'PLAIN']])
        );
    }

    public function test_handle_prefix_also_applies_to_parameters_set_on_the_tag()
    {
        $this->viewShouldReturnRaw('hero', '<{{ title }}><{{ hero_title }}>');

        $this->assertSame('<HT><HT>', $this->render('{{ include:hero handle_prefix="hero_" hero_title="HT" }}'));
    }

    public function test_a_parameter_set_on_the_tag_wins_under_both_names()
    {
        $this->viewShouldReturnRaw('hero', '<{{ title }}><{{ hero_title }}>');

        $data = ['d' => ['hero_title' => 'FROM-SPREAD']];

        $this->assertSame('<OVERRIDE><OVERRIDE>', $this->render('{{ include:hero :params="d" handle_prefix="hero_" hero_title="OVERRIDE" }}', $data));
        $this->assertSame('<OVERRIDE><FROM-SPREAD>', $this->render('{{ include:hero :params="d" handle_prefix="hero_" title="OVERRIDE" }}', $data));
    }

    public function test_handle_prefix_leaves_keys_it_would_reduce_to_nothing_alone()
    {
        $this->viewShouldReturnRaw('hero', '<{{ title }}>[{{ hero_ }}]');

        $this->assertSame(
            '<T>[X]',
            $this->render('{{ include:hero :params="d" handle_prefix="hero_" }}', ['d' => ['hero_' => 'X', 'hero_title' => 'T']])
        );
    }

    public function test_when_param_controls_rendering()
    {
        $this->viewShouldReturnRaw('greeting', 'Hello');

        $this->assertSame('', $this->render('{{ include:greeting when="false" }}'));
        $this->assertSame('Hello', $this->render('{{ include:greeting when="true" }}'));
    }

    public function test_unless_param_controls_rendering()
    {
        $this->viewShouldReturnRaw('greeting', 'Hello');

        $this->assertSame('', $this->render('{{ include:greeting unless="true" }}'));
        $this->assertSame('Hello', $this->render('{{ include:greeting unless="false" }}'));
    }
}
