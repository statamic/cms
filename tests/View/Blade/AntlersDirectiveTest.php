<?php

namespace Tests\View\Blade;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AntlersDirectiveTest extends TestCase
{
    public function test_blade_antlers_directive_is_compiled()
    {
        $template = <<<'EOT'

<?php $myCustomVariable = ['foo' => 'bar']; ?>
@php($data = range(1, 10))

@if ($test)
    @antlers
        {{ myCustomVariable }}
            {{ foo }}
        {{ /myCustomVariable }}

        {{ data }}
            {{ value }}
        {{ /data }}
    @endantlers
@endif

@php($data = range('a', 'e'))
@antlers {{ data }}{{ value }}{{ /data }} @endantlers
EOT;

        $expected = <<<'EXPECTED'
bar
        

        
            1
        
            2
        
            3
        
            4
        
            5
        
            6
        
            7
        
            8
        
            9
        
            10
        
    
 abcde
EXPECTED;

        $this->assertSame($expected, trim(Blade::render($template, ['test' => true])));
    }
}
