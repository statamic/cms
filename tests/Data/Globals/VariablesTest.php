<?php

namespace Tests\Data\Globals;

use Statamic\Globals\Variables;
use Tests\TestCase;

class VariablesTest extends TestCase
{
    /** @test */
    public function it_gets_file_contents_for_saving()
    {
        $entry = (new Variables)->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
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
