<?php

namespace Tests\Antlers\Runtime\Includes;

use Statamic\Facades\Cascade;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class IssuesTest extends ParserTestCase
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

    public function test_issue_8175_assigned_variables_never_leak_consistently()
    {
        $this->viewShouldReturnRaw('noop', '');
        $this->viewShouldReturnRaw('setter', '{{ $var = "SET" }}');
        $this->viewShouldReturnRaw('setter_extra', '{{ $var = "SET" }}{{ partial:noop }}');

        $this->assertSame('|[]', $this->render('{{ include:setter }}|[{{ $var }}]'));
        $this->assertSame('|[]', $this->render('{{ include:setter_extra }}|[{{ $var }}]'));
    }

    public function test_issue_10703_params_do_not_leak_into_the_next_include()
    {
        $this->viewShouldReturnRaw('cardA', '[{{ class }}|{{ view:class }}]');
        $this->viewShouldReturnRaw('cardB', '[{{ class }}|{{ view:class }}]');

        $this->assertSame(
            '[cool|cool][|]',
            $this->render('{{ include:cardA class="cool" }}{{ include:cardB }}')
        );
    }

    public function test_issue_11486_frontmatter_does_not_leak_across_inclusions()
    {
        $this->viewShouldReturnRaw('inc_a', "---\nvar_a: A\n---\nA[{{ view:var_a }}]");
        $this->viewShouldReturnRaw('inc_b', "---\nvar_b: B\n---\nB[{{ view:var_b }}]{{ include:inc_a }}");

        $template = '{{ include:inc_b }}{{ include:inc_b }}|HOME[{{ view:var_a }}|{{ view:var_b }}]';

        $this->assertSame('B[B]A[A]B[B]A[A]|HOME[|]', $this->render($template));
        $this->assertNull(Cascade::get('views'));
    }

    public function test_issue_12709_isolation_is_consistent_across_conditional_forms()
    {
        $this->viewShouldReturnRaw('mod', '{{ foo = "changed" }}M');

        $this->assertSame(
            'M|orig',
            $this->render('{{ foo = "orig" }}{{ if bar }}{{ include:mod }}{{ /if }}|{{ foo }}', ['bar' => true])
        );

        $this->assertSame(
            'M|orig',
            $this->render('{{ foo = "orig" }}{{ bar ?= { include:mod } }}|{{ foo }}', ['bar' => true])
        );
    }
}
