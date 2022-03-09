<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;

class InterpolationTest extends ParserTestCase
{
    public function test_interpolation_cache_is_cleared_between_nodes()
    {
        $template = <<<'EOT'
{{ partial:input id="email" }}{{ partial:input id="password" }}
EOT;

        $this->assertSame('<input id="email"><input id="password">', $this->renderString($template, [], true));
    }
}
