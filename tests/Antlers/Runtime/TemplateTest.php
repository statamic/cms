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

    /** @test */
    public function it_applies_modifier_on_different_array_syntax()
    {
        $vars = [
            'key' => 'entries',
            'source' => [
                'entries' => [
                    ['id' => 0],
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ],
            ],
        ];

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source.entries }}[{{ id }}]{{ /source.entries }}', $vars)
        );

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source[key] }}[{{ id }}]{{ /source[key] }}', $vars)
        );

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source.entries sort="id" }}[{{ id }}]{{ /source.entries }}', $vars)
        );

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source[key] sort="id" }}[{{ id }}]{{ /source[key] }}', $vars)
        );

        $this->assertEquals(
            '[3][2][1][0]',
            $this->renderString('{{ source[key] sort="id|desc" }}[{{ id }}]{{ /source[key] }}', $vars)
        );
    }
}
