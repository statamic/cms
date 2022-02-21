<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;
use Tests\View\Antlers\ParserTests;

class TemplateTest extends ParserTestCase
{
    use ParserTests;

    public function test_it_passes_data_to_php_when_enabled()
    {
        $this->assertEquals('hello', (string) $this->parser()->allowPhp(true)->parse('{{ associative }}<?php echo $one; ?>{{ /associative }}', $this->variables));
    }

    public function test_it_returns_escaped_content()
    {
        $input = 'Hey, look at that @{{ noun }}!';
        $this->assertSame('Hey, look at that {{ noun }}!', $this->renderString($input, []));
    }
}
