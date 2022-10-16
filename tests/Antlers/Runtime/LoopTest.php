<?php

namespace Tests\Antlers\Runtime;

use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;
use Tests\Antlers\ParserTestCase;
use Tests\FakesContent;
use Tests\FakesViews;

class LoopTest extends ParserTestCase
{
    use FakesViews,
        FakesContent;

    public function test_non_sequential_numeric_keys_are_not_treated_as_associative_arrays()
    {
        // Non-sequential numeric keys are not treated as associative arrays
        // with the current Antlers version. This test ensures that the
        // behavior when these arrays are consistent with Runtime.
        $data = [
            'loop_data' => [
                1 => 'Hello',
                7 => ', ',
                5 => 'wilderness',
            ],
            'other_data' => [
                'Hello',
                ', ',
                'wilderness',
            ],
            'more_data' => [
                'hello' => 'Hello',
                'space' => ', ',
                'wilderness' => 'wilderness',
            ],
        ];

        $this->assertSame('Hello, wilderness', $this->renderString('{{ loop_data }}{{ value }}{{ /loop_data }}', $data));
        $this->assertSame('Hello, wilderness', $this->renderString('{{ other_data }}{{ value }}{{ /other_data }}', $data));

        $template = <<<'EOT'
{{ more_data }}{{ hello }}{{ space }}{{ wilderness }}{{ /more_data }}
EOT;
        $this->assertSame('Hello, wilderness', $this->renderString($template, $data));
    }

    public function test_lists_can_access_next_prev_variables()
    {
        $data = [
            'articles' => [
                ['title' => 'Article One'],
                ['title' => 'Article Two'],
                ['title' => 'Article Three'],
            ],
        ];

        $template = <<<'EOT'
{{ articles }}
<{{ prev.title ?? 'No Prev' }}><{{ title }}><{{ next.title ?? 'No Next' }}>
{{ /articles }}
EOT;

        $expected = <<<'EOT'

<No Prev><Article One><Article Two>

<Article One><Article Two><Article Three>

<Article Two><Article Three><No Next>

EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data));
    }

    public function test_empty_collections_do_not_print_brackets()
    {
        $data = [
            'taxonomy' => collect(),
        ];

        $this->assertSame('', $this->renderString('{{ taxonomy }}', $data, true));
    }

    public function test_strict_variable_syntax_can_be_used_for_loops()
    {
        $cascade = $this->mock(Cascade::class, function ($m) {
            $m->shouldReceive('get')->with('theme')->andReturn([
                'social_links' => [
                    'one',
                    'two',
                    'three',
                ],
            ]);
        });

        $template = <<<'EOT'
{{ theme:social_links }}<{{ value }}>{{ /theme:social_links }}
EOT;
        $templateTwo = <<<'EOT'
{{ $theme:social_links }}<{{ value }}>{{ /$theme:social_links }}
EOT;

        $results = (string) $this->parser()->cascade($cascade)->parse($template, []);
        $resultsTwo = (string) $this->parser()->cascade($cascade)->parse($template, []);

        $this->assertSame('<one><two><three>', $results);
        $this->assertSame('<one><two><three>', $resultsTwo);
    }

    public function test_runtime_resets_data_manager_paired_state()
    {
        $value = new Value(['one', 'two', 'three']);
        $data = [
            'value' => 'a value',
            'loop' => $value,
        ];

        $isPaired = null;

        // Reset the callbacks from any other tests.
        GlobalRuntimeState::$peekCallbacks = [];
        GlobalRuntimeState::$peekCallbacks[] = function (NodeProcessor $processor) use (&$isPaired) {
            $isPaired = $processor->getPathDataManager()->getIsPaired();
        };

        $template = <<<'EOT'
{{ loop  }}
<{{ value }}>
<{{ value ensure_right="test" }}>
{{ ___internal_debug:peek }}
{{ /loop }}
EOT;

        $expected = <<<'EOT'
<one>
<onetest>


<two>
<twotest>


<three>
<threetest>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data, true)));
        $this->assertTrue($isPaired);
    }

    public function test_runtime_does_not_attempt_evaluate_modifiers_twice()
    {
        mt_srand(1234);

        $data = [
            'widths' => [
                '25',
                '50',
                '75',
            ],
        ];

        $template = <<<'EOT'
{{ loop from="1" to="10" }}<{{ value }}><{{ widths | shuffle | limit:1 }}width-{{ value }}{{ /widths }}><{{ value }}>{{ unless last }}|{{ /unless}}{{ /loop }}
EOT;

        $expected = <<<'EXPECTED'
<1><width-75><1>|<2><width-75><2>|<3><width-50><3>|<4><width-75><4>|<5><width-25><5>|<6><width-75><6>|<7><width-75><7>|<8><width-50><8>|<9><width-50><9>|<10><width-50><10>
EXPECTED;

        $this->assertSame($expected, $this->renderString($template, $data, true));

        mt_srand(1234);

        $template = <<<'EOT'
{{ loop from="1" to="10" }}<{{ value }}><{{ widths | shuffle | limit:1 }}width-{{ value }}{{ /widths | shuffle | limit:1 }}><{{ value }}>{{ unless last }}|{{ /unless }}{{ /loop }}
EOT;

        $this->assertSame($expected, $this->renderString($template, $data, true));
    }

    public function test_runtime_maintains_scope_on_nested_loops()
    {
        $this->createPage('home', [
            'with' => [
                'title' => 'Home Page',
                'template' => 'home',
            ],
        ]);

        $this->createPage('about', ['with' => ['title' => 'About Page']]);
        $this->createPage('contact', ['with' => ['title' => 'Contact Page']]);

        $template = <<<'TEMPLATE'
<start:{{ title }}>
{{ test = -1; }}
{{ outer = [1] }}
<outer_start:{{ title }}>{{ test = 0; }}
{{ pages = {collection:pages} }}
<inside_loop:{{ title }}>
{{ partial:test }}{{ test = 1; }}
{{ /pages }}
<outer_end:{{ title }}>
{{ /outer }}
<end:{{ title }}:{{ test }}>
TEMPLATE;

        $partial = <<<'PARTIAL'
<partial:{{ title }}>
PARTIAL;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('home', $template);
        $this->viewShouldReturnRaw('test', $partial);
        $expected = <<<'EXPECTED'
<start:Home Page>


<outer_start:Home Page>

<inside_loop:About Page>
<partial:About Page>

<inside_loop:Contact Page>
<partial:Contact Page>

<inside_loop:Home Page>
<partial:Home Page>

<outer_end:Home Page>

<end:Home Page:1>
EXPECTED;

        $response = $this->get('/home')
            ->assertStatus(200);

        $responseContent = trim($response->getContent());
        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            StringUtilities::normalizeLineEndings($responseContent)
        );
    }
}
