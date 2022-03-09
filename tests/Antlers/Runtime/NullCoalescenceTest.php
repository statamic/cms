<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Cascade;
use Tests\Antlers\ParserTestCase;

class NullCoalescenceTest extends ParserTestCase
{
    public function test_modifiers_can_be_called_on_a_null_coalescence_group()
    {
        $template = <<<'EOT'
{{ (seo_title ?? title) | title }}
EOT;

        $this->assertSame('I Am the the Title', $this->renderString($template, [
            'seo_title' => null,
            'title' => 'i am the the title',
        ], true));

        $this->assertSame('I Am the Seo Title', $this->renderString($template, [
            'seo_title' => 'i am the seo title',
            'title' => 'i am the the title',
        ], true));
    }

    public function test_modifiers_can_be_called_on_explicit_null_coalescence_group()
    {
        $template = <<<'EOT'
{{ (seo_title ?? title) | title }}
EOT;

        $this->assertSame('I Am the the Title', $this->renderString($template, [
            'seo_title' => null,
            'title' => 'i am the the title',
        ], true));

        $this->assertSame('I Am the Seo Title', $this->renderString($template, [
            'seo_title' => 'i am the seo title',
            'title' => 'i am the the title',
        ], true));
    }

    public function test_implicit_null_coalescence_groups_respect_logic_groups()
    {
        $template = <<<'EOT'
{{ seo_title ?? (title | title) }}
EOT;

        $this->assertSame('I Am the the Title', $this->renderString($template, [
            'seo_title' => null,
            'title' => 'i am the the title',
        ], true));

        $this->assertSame('i am the seo title', $this->renderString($template, [
            'seo_title' => 'i am the seo title',
            'title' => 'i am the the title',
        ], true));
    }

    public function test_null_coalescence_with_multi_path_parts()
    {
        $data = [
            'config' => [
                'app' => [
                    'name' => 'Statamic',
                ],
            ],
        ];

        $template = <<<'EOT'
{{ settings:copyright_name ?? config:app:name }}
EOT;

        $this->assertSame('Statamic', $this->renderString($template, $data));

        $cascade = $this->mock(Cascade::class, function ($m) {
            $m->shouldReceive('get')->with('settings')->once()->andReturn(null);
        });

        $this->assertSame('Statamic', (string) $this->parser()->cascade($cascade)->render($template, $data));
    }
}
