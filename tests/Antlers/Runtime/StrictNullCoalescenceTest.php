<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Cascade;
use Tests\Antlers\ParserTestCase;

class StrictNullCoalescenceTest extends ParserTestCase
{
    public function test_strict_null_falls_through()
    {
        $template = <<<'EOT'
{{ a ??? b }}
EOT;

        $this->assertSame('fallback', $this->renderString($template, [
            'a' => null,
            'b' => 'fallback',
        ]));
    }

    public function test_empty_string_is_preserved()
    {
        $template = <<<'EOT'
{{ a ??? b }}
EOT;

        $this->assertSame('', $this->renderString($template, [
            'a' => '',
            'b' => 'fallback',
        ]));
    }

    public function test_zero_is_preserved()
    {
        $template = <<<'EOT'
{{ a ??? b }}
EOT;

        $this->assertSame('0', $this->renderString($template, [
            'a' => 0,
            'b' => 'fallback',
        ]));

        $this->assertSame('0', $this->renderString($template, [
            'a' => '0',
            'b' => 'fallback',
        ]));
    }

    public function test_false_is_preserved()
    {
        $template = <<<'EOT'
{{ a ??? b }}
EOT;

        $this->assertSame('', $this->renderString($template, [
            'a' => false,
            'b' => 'fallback',
        ]));
    }

    public function test_undefined_variable_falls_through()
    {
        $template = <<<'EOT'
{{ missing ??? 'fallback' }}
EOT;

        $this->assertSame('fallback', $this->renderString($template));
    }

    public function test_chaining_with_all_null_returns_last()
    {
        $template = <<<'EOT'
{{ a ??? b ??? 'final' }}
EOT;

        $this->assertSame('final', $this->renderString($template, [
            'a' => null,
            'b' => null,
        ]));
    }

    public function test_chaining_returns_first_non_null()
    {
        $template = <<<'EOT'
{{ a ??? b ??? 'final' }}
EOT;

        $this->assertSame('', $this->renderString($template, [
            'a' => null,
            'b' => '',
        ]));

        $this->assertSame('0', $this->renderString($template, [
            'a' => null,
            'b' => 0,
        ]));
    }

    public function test_mixing_with_loose_null_coalescence()
    {
        $template = <<<'EOT'
{{ a ?? b ??? 'final' }}
EOT;

        $this->assertSame('final', $this->renderString($template, [
            'a' => '',
            'b' => null,
        ]));

        $this->assertSame('B', $this->renderString($template, [
            'a' => null,
            'b' => 'B',
        ]));
    }

    public function test_chaining_is_left_associative_with_mixed_operators()
    {
        // Grouping is (a ??? b) ?? c. The strict inner group preserves 0,
        // but the outer loose ?? then treats 0 as null-like and falls through to c.
        $template = <<<'EOT'
{{ a ??? b ?? c }}
EOT;

        $this->assertSame('C', $this->renderString($template, [
            'a' => 0,
            'b' => 'B',
            'c' => 'C',
        ]));

        // When the strict inner group returns a truthy value, the outer ?? keeps it.
        $this->assertSame('B', $this->renderString($template, [
            'a' => null,
            'b' => 'B',
            'c' => 'C',
        ]));
    }

    public function test_modifiers_can_be_called_on_strict_group()
    {
        $template = <<<'EOT'
{{ (seo_title ??? title) | upper }}
EOT;

        $this->assertSame('I AM THE TITLE', $this->renderString($template, [
            'seo_title' => null,
            'title' => 'i am the title',
        ], true));

        $this->assertSame('I AM THE SEO TITLE', $this->renderString($template, [
            'seo_title' => 'i am the seo title',
            'title' => 'i am the title',
        ], true));
    }

    public function test_strict_null_coalescence_with_multi_path_parts()
    {
        $data = [
            'config' => [
                'app' => [
                    'name' => 'Statamic',
                ],
            ],
        ];

        $template = <<<'EOT'
{{ settings:copyright_name ??? config:app:name }}
EOT;

        $this->assertSame('Statamic', $this->renderString($template, $data));

        $cascade = $this->mock(Cascade::class, function ($m) {
            $m->shouldReceive('get')->with('settings')->andReturn(null);
        });

        $this->assertSame('Statamic', (string) $this->parser()->cascade($cascade)->render($template, $data));
    }

    public function test_strict_null_coalescence_short_circuits_right_side()
    {
        $template = <<<'EOT'
{{ hello = "Hello" }}{{ world = "World" }}{{ hello ??? (world = "Earth") }} {{ world }}
EOT;

        $this->assertSame('Hello World', $this->renderString($template, [], true));
    }

    public function test_strict_vs_loose_divergence()
    {
        $loose = <<<'EOT'
{{ a ?? 'fallback' }}
EOT;

        $strict = <<<'EOT'
{{ a ??? 'fallback' }}
EOT;

        $falsyValues = [
            ['a' => 0],
            ['a' => false],
        ];

        foreach ($falsyValues as $data) {
            $this->assertSame('fallback', $this->renderString($loose, $data));
            $this->assertNotSame('fallback', $this->renderString($strict, $data));
        }

        $nullData = ['a' => null];
        $this->assertSame('fallback', $this->renderString($loose, $nullData));
        $this->assertSame('fallback', $this->renderString($strict, $nullData));
    }
}
