<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Collection;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\View\Antlers\NonArrayableObject;
use Tests\View\Antlers\ParserTests;

class TemplateTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesViews;
    use ParserTests;

    /** @test */
    public function non_arrays_cannot_be_looped()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ string }}', [
                'line' => 1, 'file' => '',
            ]);

        $template = '{{ string }} {{ /string }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    /** @test */
    public function unclosed_array_variable_pairs_should_be_null()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an array variable as a string: {{ simple }}', [
                'line' => 1, 'file' => '',
            ]);

        $template = '{{ simple }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    public function test_rendering_a_non_array_variable_reports_current_file()
    {
        $this->markTestSkipped(); // todo

        Log::shouldReceive('debug')->once()->with('Cannot render an array variable as a string: {{ an_array_value }}', [
            'line' => 3, 'file' => 'the_partial.antlers.html',
        ]);

        Collection::make('pages')->routes(['en' => '/{{slug}}'])->save();
        EntryFactory::collection('pages')->id('1')->slug('home')->data(['title' => 'Home'])->create();
        $this->withFakeViews();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $default = <<<'DEFAULT'
Some content
more content
even more content
hey look a wild partial appears! {{ partial:the_partial }}
DEFAULT;
        $this->viewShouldReturnRaw('default', $default);
        $thePartial = <<<'PARTIAL'
{{ an_array_value = [1,2,3,4,5]; }}

Attempt to render as a string: {{ an_array_value }}
PARTIAL;
        $this->viewShouldReturnRaw('the_partial', $thePartial);
        $this->get('/home')->assertOk();
    }

    /** @test */
    public function it_doesnt_output_anything_if_object_cannot_be_cast_to_a_string()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an object variable as a string: {{ object }}', [
                'line' => 1, 'file' => '',
            ]);

        $object = new class
        {
        };

        $this->assertEquals('', $this->renderString('{{ object }}', compact('object')));
    }

    /** @test */
    public function it_cannot_cast_non_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ object }}', [
                'line' => 1, 'file' => '',
            ]);

        $nonArrayableObject = new NonArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            '',
            $this->renderString('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $nonArrayableObject,
            ])
        );
    }

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
