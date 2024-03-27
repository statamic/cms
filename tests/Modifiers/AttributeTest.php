<?php

namespace Tests\Modifiers;

use Statamic\Facades\Parse;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    protected $data = [
        'view' => [
            'class_list' => 'text-pink-500 bg-ugly-200',
        ],
        'link_target' => '_blank',
        'link_rel' => ' ',
        'bool_true' => true,
        'bool_false' => false,
        'integer' => -1,
        'array' => [],
    ];

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_returns_the_attribute_when_view_value_is_not_empty()
    {
        $template = <<<'EOT'
{{ view:class_list | attribute:class }}
EOT;

        $this->assertSame(
            ' class="text-pink-500 bg-ugly-200"',
            $this->tag($template, $this->data)
        );
    }

    /** @test */
    public function it_returns_the_attribute_when_scoped_value_is_not_empty()
    {
        $template = <<<'EOT'
{{ link_target | attribute:target }}
EOT;

        $this->assertSame(
            ' target="_blank"',
            $this->tag($template, $this->data)
        );
    }

    /** @test */
    public function it_returns_the_attribute_when_value_is_an_integer()
    {
        $template = <<<'EOT'
{{ integer | attribute:tabIndex }}
EOT;

        $this->assertSame(
            ' tabIndex="-1"',
            $this->tag($template, $this->data)
        );
    }

    /** @test */
    public function it_returns_an_empty_string_when_value_is_empty()
    {
        $template = <<<'EOT'
{{ link_rel | attribute:rel }}
EOT;

        $this->assertSame(
            '',
            $this->tag($template, $this->data)
        );
    }

    /** @test */
    public function it_returns_the_attribute_alone_when_value_is_bool_true()
    {
        $template = <<<'EOT'
{{ bool_true | attribute:required }}
EOT;

        $this->assertSame(
            ' required',
            $this->tag($template, $this->data)
        );
    }

    /** @test */
    public function it_returns_an_empty_string_when_value_is_bool_false()
    {
        $template = <<<'EOT'
{{ bool_false | attribute:required }}
EOT;

        $this->assertSame(
            '',
            $this->tag($template, $this->data)
        );
    }
}
