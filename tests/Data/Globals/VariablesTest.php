<?php

namespace Tests\Data\Globals;

use Tests\TestCase;
use Statamic\Globals\Variables;

class VariablesTest extends TestCase
{
    /** @test */
    function it_gets_file_contents_for_saving()
    {
        $entry = (new Variables)->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string'
        ]);

        $expected = <<<'EOT'
array:
  - 'first one'
  - 'second one'
string: 'The string'

EOT;
        $this->assertEquals($expected, $entry->fileContents());
    }
}
