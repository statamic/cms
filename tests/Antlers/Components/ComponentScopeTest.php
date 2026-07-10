<?php

namespace Tests\Antlers\Components;

use Tests\Antlers\ParserTestCase;

class ComponentScopeTest extends ParserTestCase
{
    public function test_variables_do_not_leak_into_components()
    {
        $template = <<<'EOT'
{{ the_var = 'a value!'; }}
<x-scope.antlers />
<x-scope.blade />
{{ the_var }}
EOT;

        $expected = <<<'EOT'

nope
nope
a value!
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template)
        );
    }

    public function test_variables_do_not_leak_out_of_components()
    {
        $template = <<<'EOT'
{{ the_var = null; }}
<x-scope.antlers_set />
<x-scope.blade_set />
{{ the_var ?? 'nope' }}
EOT;

        $result = trim($this->renderString($template));

        $this->assertSame('nope', $result);
    }
}
