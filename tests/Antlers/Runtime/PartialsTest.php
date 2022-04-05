<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class PartialsTest extends ParserTestCase
{
    use FakesViews;

    public function test_nested_partials_render_correctly()
    {
        $template = <<<'EOT'
{{ partial src="wrapper" }}
    {{ partial src="second_wrapper" }}
        {{ partial src="content" /}}
    {{ /partial }}
{{ /partial }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('wrapper', '{{ slot }}');
        $this->viewShouldReturnRaw('second_wrapper', '');
        $this->viewShouldReturnRaw('content', 'My content');

        $this->assertSame('', $this->renderString($template));
    }
}