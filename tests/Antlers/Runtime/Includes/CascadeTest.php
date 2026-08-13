<?php

namespace Tests\Antlers\Runtime\Includes;

use Illuminate\Support\Facades\Blade;
use RuntimeException;
use Statamic\Facades\Cascade;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class CascadeTest extends ParserTestCase
{
    use FakesViews;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withFakeViews();

        Cascade::set('cval', 'C');
    }

    private function render($template, $data = [])
    {
        return $this->renderString($template, $data, true);
    }

    public function test_a_view_only_reaches_the_cascade_when_it_asks_to()
    {
        $this->viewShouldReturnRaw('x', 'X[{{ cval }}]');

        $this->assertSame('X[]', $this->render('{{ include:x }}'));
        $this->assertSame('X[C]', $this->render('{{ include:x cascade="true" }}'));
    }

    public function test_the_caller_keeps_the_cascade_on_both_sides_of_an_include()
    {
        $this->viewShouldReturnRaw('x', 'X');

        $this->assertSame('[C]X[C]', $this->render('[{{ cval }}]{{ include:x }}[{{ cval }}]'));
    }

    public function test_the_caller_keeps_the_cascade_after_an_include_in_a_loop()
    {
        $this->viewShouldReturnRaw('r', 'R');

        $this->assertSame('RR[C]', $this->render('{{ items }}{{ include:r }}{{ /items }}[{{ cval }}]', ['items' => [[], []]]));
    }

    public function test_a_nested_include_does_not_inherit_the_cascade()
    {
        $this->viewShouldReturnRaw('l1', 'L1[{{ cval }}]{{ include:l2 }}');
        $this->viewShouldReturnRaw('l2', 'L2[{{ cval }}]');

        $this->assertSame('L1[C]L2[]', $this->render('{{ include:l1 cascade="true" }}'));
    }

    public function test_a_nested_include_does_not_enable_the_cascade_for_its_parent()
    {
        $this->viewShouldReturnRaw('outer', '[{{ cval }}]{{ include:inner }}[{{ cval }}]');
        $this->viewShouldReturnRaw('inner', '[{{ cval }}]');

        $this->assertSame('[][][]', $this->render('{{ include:outer }}'));
    }

    public function test_a_nested_include_can_still_opt_in()
    {
        $this->viewShouldReturnRaw('l1', 'L1[{{ cval }}]{{ include:l2 cascade="true" }}');
        $this->viewShouldReturnRaw('l2', 'L2[{{ cval }}]');

        $this->assertSame('L1[]L2[C]', $this->render('{{ include:l1 }}'));
    }

    public function test_slot_contents_resolve_cascade_values_like_the_caller_does()
    {
        $this->viewShouldReturnRaw('default', '<w>{{ slot }}</w>');
        $this->viewShouldReturnRaw('named', '<w>{{ slot:h }}</w>');
        $this->viewShouldReturnRaw('scoped', '{{ slot:h :n="1" }}');

        $this->assertSame('<w>[C]</w>', $this->render('{{ include:default }}[{{ cval }}]{{ /include:default }}'));
        $this->assertSame('<w>[C]</w>', $this->render('{{ include:named }}{{ slot:h }}[{{ cval }}]{{ /slot:h }}{{ /include:named }}'));
        $this->assertSame('[C|1]', $this->render('{{ include:scoped }}{{ slot:h }}[{{ cval }}|{{ n }}]{{ /slot:h }}{{ /include:scoped }}'));
    }

    public function test_slot_contents_written_inside_a_view_use_that_views_cascade_state()
    {
        $this->viewShouldReturnRaw('outer', '{{ include:wrapper }}[{{ cval }}]{{ /include:wrapper }}');
        $this->viewShouldReturnRaw('wrapper', '<w>{{ slot }}</w>');

        $this->assertSame('<w>[]</w>', $this->render('{{ include:outer }}'));
        $this->assertSame('<w>[C]</w>', $this->render('{{ include:outer cascade="true" }}'));
    }

    public function test_a_partial_rendered_inside_an_include_follows_the_includes_cascade_state()
    {
        $this->viewShouldReturnRaw('x', 'X[{{ cval }}]{{ partial:p }}');
        $this->viewShouldReturnRaw('p', 'P[{{ cval }}]');

        $this->assertSame('X[]P[]|[C]', $this->render('{{ include:x }}|[{{ cval }}]'));
        $this->assertSame('X[C]P[C]|[C]', $this->render('{{ include:x cascade="true" }}|[{{ cval }}]'));
    }

    public function test_an_include_inside_a_partial_leaves_the_partials_cascade_alone()
    {
        $this->viewShouldReturnRaw('p', 'P[{{ cval }}]{{ include:x }}P[{{ cval }}]');
        $this->viewShouldReturnRaw('x', 'X');

        $this->assertSame('P[C]XP[C][C]', $this->render('{{ partial:p }}[{{ cval }}]'));
    }

    public function test_a_blade_include_leaves_the_surrounding_antlers_cascade_alone()
    {
        $this->viewShouldReturnRaw('b', 'B[{{ $cval ?? "" }}]', 'blade.php');

        $this->assertSame('[C]B[][C]', $this->render('[{{ cval }}]{{ include:b }}[{{ cval }}]'));
        $this->assertSame('[C]B[C][C]', $this->render('[{{ cval }}]{{ include:b cascade="true" }}[{{ cval }}]'));
    }

    public function test_blade_includes_reach_the_cascade_when_they_ask_to()
    {
        $this->viewShouldReturnRaw('b', 'B[{{ $cval ?? "" }}]', 'blade.php');

        $this->assertSame('B[]', Blade::render('<s:include:b />'));
        $this->assertSame('B[C]', Blade::render('<s:include:b cascade="true" />'));
    }

    public function test_runtime_state_survives_an_exception_thrown_inside_an_include()
    {
        (new class extends Tags
        {
            protected static $handle = 'explode';

            public function index()
            {
                throw new RuntimeException('boom');
            }
        })::register();

        $this->viewShouldReturnRaw('boom', '{{ explode }}');
        $this->viewShouldReturnRaw('ok', 'OK');

        try {
            $this->render('{{ include:boom }}');
            $this->fail('The exception should not have been swallowed.');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertTrue(GlobalRuntimeState::$isCascadeEnabled);
        $this->assertFalse(GlobalRuntimeState::$requiresRuntimeIsolation);
        $this->assertNull(Cascade::get('views'));
        $this->assertSame('OK[C]', $this->render('{{ include:ok }}[{{ cval }}]'));
    }

    public function test_runtime_state_survives_an_exception_thrown_inside_a_deferred_slot_render()
    {
        (new class extends Tags
        {
            protected static $handle = 'slot_boom';

            public function index()
            {
                throw new RuntimeException('boom');
            }
        })::register();

        $this->viewShouldReturnRaw('w', '<w>{{ slot }}</w>');
        $this->viewShouldReturnRaw('ok', 'OK');

        try {
            $this->render('{{ include:w }}{{ slot_boom }}{{ /include:w }}');
            $this->fail('The exception should not have been swallowed.');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertTrue(GlobalRuntimeState::$isCascadeEnabled);
        $this->assertSame([], GlobalRuntimeState::$prefixState);
        $this->assertFalse(GlobalRuntimeState::$requiresRuntimeIsolation);
        $this->assertSame('OK[C]', $this->render('{{ include:ok }}[{{ cval }}]'));
    }

    public function test_any_isolated_tag_restores_the_previous_cascade_state()
    {
        $tag = new class extends Tags
        {
            protected static $handle = 'some_isolated_tag';

            public static $isolated = true;

            public function index()
            {
                return '';
            }
        };
        $tag::register();

        $probe = new class extends Tags
        {
            protected static $handle = 'cascade_probe';

            public static $seen = null;

            public function index()
            {
                self::$seen = GlobalRuntimeState::$isCascadeEnabled;

                return '';
            }
        };
        $probe::register();

        $this->viewShouldReturnRaw('x', '{{ some_isolated_tag }}{{ cascade_probe }}');

        $this->render('{{ include:x }}');

        $this->assertFalse(
            $probe::$seen,
            'An isolated tag must not hand cascade access back to a caller that had isolated itself.'
        );
    }

    public function test_an_include_restores_the_previous_handle_prefixes()
    {
        $tag = new class extends Tags
        {
            protected static $handle = 'prefix_probe';

            public static $seen = null;

            public function index()
            {
                self::$seen = GlobalRuntimeState::$prefixState;

                return '';
            }
        };
        $tag::register();

        $this->viewShouldReturnRaw('probe', '{{ prefix_probe }}');

        GlobalRuntimeState::$prefixState = ['hero_'];

        try {
            $this->render('{{ include:probe }}');

            $this->assertSame([], $tag::$seen, 'An include should not inherit the caller\'s handle prefixes.');
            $this->assertSame(['hero_'], GlobalRuntimeState::$prefixState);
        } finally {
            GlobalRuntimeState::$prefixState = [];
        }
    }

    public function test_resetting_global_state_restores_cascade_access()
    {
        GlobalRuntimeState::$isCascadeEnabled = false;

        GlobalRuntimeState::resetGlobalState();

        $this->assertTrue(GlobalRuntimeState::$isCascadeEnabled);
    }
}
