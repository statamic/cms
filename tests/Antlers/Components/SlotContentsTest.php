<?php

namespace Tests\Antlers\Components;

use Tests\Antlers\ParserTestCase;

class SlotContentsTest extends ParserTestCase
{
    public function test_component_instance_is_available_in_slots()
    {
        $template = <<<'EOT'
<x-basic>{{ component.componentName }}</x-basic>
EOT;

        $this->assertSame(
            'basic',
            $this->renderString($template)
        );
    }

    public function test_component_instance_is_not_available_after_component()
    {
        $template = <<<'EOT'
<x-basic>{{ component.componentName }}</x-basic> After: {{ component.componentName ?? 'nope' }}
EOT;

        $this->assertSame(
            'basic After: nope',
            $this->renderString($template)
        );
    }

    public function test_blade_components_named_slots()
    {
        $template = <<<'EOT'
<x-named_slots>
    <x-slot:header class="header-classes">
        Header Content
    </x-slot:header>
    <x-slot:footer class="footer-classes">
        Footer Content
    </x-slot:footer>
    
    Slot Content
</x-named_slots>
EOT;

        $expected = <<<'EXPECTED'
<div class="header-classes">
  Header Content
</div>
Slot Content
<div class="footer-classes">
  Footer Content
</div>
EXPECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template)
        );
    }

    public function test_component_instance_is_available_in_named_slots()
    {
        $template = <<<'EOT'
<x-named_slots>
    <x-slot:header class="header-classes">
        Header: {{ component.componentName }}
    </x-slot:header>
    <x-slot:footer class="footer-classes">
        Footer: {{ component.componentName }}
    </x-slot:footer>
    
    Slot: {{ component.componentName }}
</x-named_slots>
EOT;

        $expected = <<<'EXPECTED'
<div class="header-classes">
  Header: named_slots
</div>
Slot: named_slots
<div class="footer-classes">
  Footer: named_slots
</div>
EXPECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template)
        );
    }

    public function test_has_actual_content_modifier()
    {
        $template = <<<'EOT'
<x-checks_content_modifier>
    <!-- nope nope nope -->
</x-checks_content_modifier>
EOT;

        $this->assertSame('No', $this->renderString($template));

        $template = <<<'EOT'
<x-checks_content_modifier>
    <!-- nope nope nope --> Something
</x-checks_content_modifier>
EOT;

        $this->assertSame('Yes', $this->renderString($template));
    }

    public function test_has_actual_content_method()
    {
        $template = <<<'EOT'
<x-checks_content_method>
    <!-- nope nope nope -->
</x-checks_content_method>
EOT;

        $this->assertSame('No', $this->renderString($template));

        $template = <<<'EOT'
<x-checks_content_method>
    <!-- nope nope nope --> Something
</x-checks_content_method>
EOT;

        $this->assertSame('Yes', $this->renderString($template));
    }
}
