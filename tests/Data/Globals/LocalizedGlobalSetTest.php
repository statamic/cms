<?php

namespace Tests\Data\Globals;

use Tests\TestCase;
use Statamic\Data\Globals\LocalizedGlobalSet;

class LocalizedGlobalSetTest extends TestCase
{
    /** @test */
    function it_gets_file_contents_for_saving()
    {
        $entry = (new LocalizedGlobalSet)->data([
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
