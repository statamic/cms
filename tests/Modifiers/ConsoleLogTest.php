<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ConsoleLogTest extends TestCase
{
    /** @test */
    public function it_wraps_the_content_into_JS_console_log_statement(): void
    {
        $arr = [
            'apples',
            'bananas',
            'bacon',
        ];
        $modified = $this->modify($arr);
        $expected = '<script>
            window.log=function(a){if(this.console){console.log(a);}};
            log('.json_encode($arr).');
        </script>';
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->consoleLog()->fetch();
    }
}
