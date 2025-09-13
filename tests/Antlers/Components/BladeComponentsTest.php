<?php

namespace Tests\Antlers\Components;

use Illuminate\Support\Facades\Blade;
use Tests\Antlers\Fixtures\Components\Card;
use Tests\Antlers\ParserTestCase;

class BladeComponentsTest extends ParserTestCase
{
    public function test_basic_blade_components_are_rendered_in_antlers()
    {
        $template = <<<'EOT'
<x-alert title="The Title" />
EOT;

        $this->assertSame(
            '<p>The Alert: The Title</p>',
            $this->renderString($template)
        );
    }

    public function test_it_renders_class_based_components_using_antlers()
    {
        Blade::component(Card::class);

        $template = <<<'EOT'
<x-card title="The Title" class="mt-4" />
EOT;

        $expected = <<<'EXPECTED'
Title: THE TITLE
Attributes: class="mt-4"
EXPECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template)
        );
    }

    public function test_aware_works_when_rendering_blade_components()
    {
        $template = <<<'EOT'
<x-aware_blade variant="md">
    <x-aware_blade.nested />
</x-aware_blade>
EOT;

        $this->assertSame(
            'The Variant: md',
            $this->renderString($template)
        );
    }

    public function test_aware_works_with_antlers_blade_components()
    {
        $template = <<<'EOT'
<x-aware_antlers variant="md">
    <x-aware_antlers.nested />
</x-aware_antlers>
EOT;

        $this->assertSame(
            'The Variant: md',
            $this->renderString($template)
        );
    }

    public function test_aware_works_with_blade_root_antlers_inner()
    {
        $template = <<<'EOT'
<x-aware_blade variant="md">
    <x-aware_antlers.nested />
</x-aware_blade>
EOT;

        $this->assertSame(
            'The Variant: md',
            $this->renderString($template)
        );
    }

    public function test_aware_works_with_antlers_root_blade_inner()
    {
        $template = <<<'EOT'
<x-aware_antlers variant="md">
    <x-aware_blade.nested />
</x-aware_antlers>
EOT;

        $this->assertSame(
            'The Variant: md',
            $this->renderString($template)
        );
    }

    public function test_components_blade_compatibility()
    {
        $template = <<<'EOT'
<x-aware_antlers variant="md">
    <x-aware_blade.nested />
</x-aware_antlers>
EOT;

        $this->assertSame(
            'The Variant: md',
            Blade::render($template)
        );

        Blade::component(Card::class);

        $template = <<<'EOT'
<x-card title="The Title" class="mt-4" />
EOT;

        $expected = <<<'EXPECTED'
Title: THE TITLE
Attributes: class="mt-4"
EXPECTED;

        $this->assertSame(
            $expected,
            Blade::render($template)
        );
    }

    public function test_method_calls_across_lines_with_attributes()
    {
        $template = <<<'EOT'
<x-attributes_dx class="one two three" data-thing="that thing">The Content</x-attributes_dx>
EOT;

        $expected = <<<'EXPECTED'
<div class="another-one hello-there! one two three" data-thing="that thing">
  The Content
</div>
EXPECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template)
        );
    }
}
