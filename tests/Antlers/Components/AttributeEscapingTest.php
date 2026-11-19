<?php

namespace Tests\Antlers\Components;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Antlers\ParserTestCase;

class AttributeEscapingTest extends ParserTestCase
{
    protected $value = 'a "b" <c> & d';

    public static function componentProvider()
    {
        return [
            'antlers component' => ['escaped_attributes'],
            'blade component' => ['escaped_attributes_blade'],
        ];
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function bound_attributes_are_escaped($component)
    {
        $this->assertSame(
            '<div data-value="a &quot;b&quot; &lt;c&gt; &amp; d"></div>',
            trim($this->renderString('<x-'.$component.' :data-value="value" />', ['value' => $this->value]))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function shorthand_bound_attributes_are_escaped($component)
    {
        $this->assertSame(
            '<div value="a &quot;b&quot; &lt;c&gt; &amp; d"></div>',
            trim($this->renderString('<x-'.$component.' :$value />', ['value' => $this->value]))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function bound_attributes_match_blade($component)
    {
        $this->assertSame(
            trim(Blade::render('<x-'.$component.' :data-value="$value" />', ['value' => $this->value])),
            trim($this->renderString('<x-'.$component.' :data-value="value" />', ['value' => $this->value]))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function literal_attributes_are_not_escaped_again($component)
    {
        $this->assertSame(
            '<div data-value="a &amp; b"></div>',
            trim($this->renderString('<x-'.$component.' data-value="a &amp; b" />'))
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function bound_props_are_not_escaped($component)
    {
        $this->assertSame(
            '<div >a "b" <c> & d</div>',
            trim($this->renderString('<x-'.$component.' :title="value" />', ['value' => $this->value]))
        );
    }

    #[Test]
    public function bound_slot_attributes_are_escaped()
    {
        $template = <<<'EOT'
<x-named_slots>
    <x-slot:header :data-value="value">Header</x-slot:header>
    <x-slot:footer>Footer</x-slot:footer>
</x-named_slots>
EOT;

        $this->assertStringContainsString(
            '<div data-value="a &quot;b&quot; &lt;c&gt; &amp; d">',
            $this->renderString($template, ['value' => $this->value])
        );
    }

    #[Test]
    #[DataProvider('componentProvider')]
    public function dynamic_component_attributes_are_escaped_once($component)
    {
        $this->assertSame(
            '<div data-value="a &quot;b&quot; &lt;c&gt; &amp; d"></div>',
            trim($this->renderString('<x-dynamic-component component="'.$component.'" :data-value="value" />', ['value' => $this->value]))
        );
    }
}
