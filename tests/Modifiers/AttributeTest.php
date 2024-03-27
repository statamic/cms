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
        'escape' => '{<!&>}',
        'bool_true' => true,
        'bool_false' => false,
        'integer' => -1,
        'float' => 1.5,
        'array' => ['array' => 'will not be handle'],
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
    public function it_returns_the_attribute_with_escaped_chars()
    {
        $template = <<<'EOT'
{{ escape | attribute:x-data }}
EOT;

        $this->assertSame(
            ' x-data="{&lt;!&amp;&gt;}"',
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
    public function it_returns_the_attribute_when_value_is_an_float()
    {
        $template = <<<'EOT'
{{ float | attribute:ratio }}
EOT;

        $this->assertSame(
            ' ratio="1.5"',
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

    /** @test */
    public function it_returns_an_empty_string_when_value_is_an_array()
    {
        $template = <<<'EOT'
{{ array | attribute:required }}
EOT;

        $this->assertSame(
            '',
            $this->tag($template, $this->data)
        );
    }

    /** @test */
    public function it_returns_an_empty_string_when_value_is_an_object_without_toString_method()
    {
        $template = <<<'EOT'
{{ object | attribute:data-req }}
EOT;

        $this->assertSame(
            '',
            $this->tag($template, ['object' => new AttributeTestNotStringable()])
        );
    }

    /** @test */
    public function it_returns_an_empty_string_when_value_is_an_object_with_toString_method()
    {
        $template = <<<'EOT'
{{ object | attribute:data-req }}
EOT;

        $this->assertSame(
            ' data-req="Test"',
            $this->tag($template, ['object' => new AttributeTestStringable()])
        );
    }
}

class AttributeTestStringable
{
    public function __toString()
    {
        return 'Test';
    }
}

class AttributeTestNotStringable
{
}
