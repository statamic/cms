<?php

namespace Tests\Antlers\Runtime\Includes;

use Statamic\Facades\Cascade;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class SandboxTest extends ParserTestCase
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

    public function test_an_enclosing_partials_handle_prefix_does_not_reach_the_include()
    {
        $this->viewShouldReturnRaw('shell', '{{ include:leaf :params="d" }}');
        $this->viewShouldReturnRaw('leaf', 'leaf[{{ title }}]');

        $data = ['d' => ['hero_title' => 'PREFIXED']];

        $this->assertSame('leaf[]', $this->render('{{ partial:shell handle_prefix="hero_" :d="d" }}', $data));
        $this->assertSame('leaf[]', $this->render('{{ scope:s handle_prefix="hero_" }}{{ include:leaf :params="d" }}{{ /scope:s }}', $data));
        $this->assertSame('leaf[]', $this->render('{{ include:leaf :params="d" }}', $data));
    }

    public function test_an_enclosing_handle_prefix_still_applies_to_slot_contents()
    {
        $this->viewShouldReturnRaw('shell', '{{ include:w }}[{{ title }}]{{ /include:w }}');
        $this->viewShouldReturnRaw('w', 'W<{{ slot }}>');

        $this->assertSame('W<[T]>', $this->render('{{ partial:shell handle_prefix="hero_" :hero_title="t" }}', ['t' => 'T']));
    }

    public function test_a_deferred_slot_render_does_not_destroy_the_views_scope()
    {
        $this->viewShouldReturnRaw('outer', '[pre={{ av }}]<{{ slot }}>[post={{ av }}][params={{ params:av }}]');
        $this->viewShouldReturnRaw('inner', 'I<{{ slot }}>');

        $this->assertSame(
            '[pre=AV]<I<X>>[post=AV][params=AV]',
            $this->render('{{ include:outer av="AV" }}{{ include:inner }}X{{ /include:inner }}{{ /include:outer }}')
        );

        $this->assertSame(
            '[pre=AV]<I<X>>[post=AV][params=AV]',
            $this->render('{{ include:outer av="AV" }}{{ partial:inner }}X{{ /partial:inner }}{{ /include:outer }}')
        );
    }

    public function test_a_partial_inside_an_include_cannot_see_the_outer_caller_scope()
    {
        $this->viewShouldReturnRaw('shell', 'S[{{ p }}]{{ partial:inner }}');
        $this->viewShouldReturnRaw('inner', 'P[{{ p }}]');

        $this->assertSame(
            'S[param]P[param]',
            $this->render('{{ include:shell p="param" }}', ['p' => 'caller-p'])
        );
    }

    public function test_a_partials_assignment_cannot_escape_the_include_boundary()
    {
        $this->viewShouldReturnRaw('shell', '{{ partial:setter }}IN[{{ v }}]');
        $this->viewShouldReturnRaw('setter', '{{ v = "from-partial" }}');

        $this->assertSame(
            'IN[]|OUT[caller]',
            $this->render('{{ v = "caller" }}{{ include:shell }}|OUT[{{ v }}]')
        );
    }

    public function test_the_internal_slot_carrier_key_is_not_exposed_to_the_view()
    {
        $this->viewShouldReturnRaw('v', 'C[{{ __statamic_include_slots }}]');

        $this->assertSame('C[]', $this->render('{{ include:v }}body{{ /include:v }}'));
    }

    public function test_mutating_a_passed_array_does_not_affect_the_caller()
    {
        $this->viewShouldReturnRaw('mut', '{{ data:key = "mutated" }}IN[{{ data:key }}]');

        $this->assertSame(
            'IN[mutated]|OUT[original]',
            $this->render('{{ include:mut :data="data" }}|OUT[{{ data:key }}]', ['data' => ['key' => 'original']])
        );
    }

    public function test_mutating_a_passed_object_does_not_affect_the_caller()
    {
        $obj = new \stdClass();
        $obj->prop = 'original';

        $this->viewShouldReturnRaw('omut', '{{ o:prop = "mutated" }}IN[{{ o:prop }}]');

        $result = $this->render('{{ include:omut :o="o" }}|OUT[{{ o:prop }}]', ['o' => $obj]);

        $this->assertSame('IN[]|OUT[original]', $result);
        $this->assertSame('original', $obj->prop, 'The underlying PHP object must not be mutated.');
    }

    public function test_self_closing_slot_output_avoids_same_name_pairing()
    {
        $this->viewShouldReturnRaw('outer', '<t>{{ slot:title /}}</t>{{ include:inner }}{{ slot:title }}INNER{{ /slot:title }}{{ /include:inner }}');
        $this->viewShouldReturnRaw('inner', '<it>{{ slot:title /}}</it>');

        $template = '{{ include:outer }}{{ slot:title }}OUTER{{ /slot:title }}{{ /include:outer }}';

        $this->assertSame('<t>OUTER</t><it>INNER</it>', $this->render($template));
    }

    public function test_slot_content_cannot_see_include_internal_variables()
    {
        $this->viewShouldReturnRaw('w', '{{ internal = "secret" }}<w>{{ slot }}</w>');

        $this->assertSame('<w>[]</w>', $this->render('{{ include:w }}[{{ internal }}]{{ /include:w }}'));
    }

    public function test_scope_tag_writes_are_visible_outside_the_include()
    {
        $this->viewShouldReturnRaw('writer', '{{ scope:smuggled }}{{ secret }}{{ /scope:smuggled }}W');

        $this->assertSame(
            'SW|CALLER[S]',
            $this->render('{{ include:writer secret="S" }}|CALLER[{{ smuggled:secret }}]')
        );
        $this->assertSame('S', Cascade::get('smuggled')['secret']);
    }

    public function test_a_slot_that_escapes_the_include_can_still_render_afterwards()
    {
        $this->viewShouldReturnRaw('w', '{{ internal = "view-secret" }}{{ scope:smuggled }}W{{ /scope:smuggled }}');

        $this->assertSame(
            'W|LATER[BODY:O:]',
            $this->render('{{ include:w }}BODY:{{ outer }}:{{ internal }}{{ /include:w }}|LATER[{{ smuggled:slot }}]', ['outer' => 'O'])
        );
    }
}
