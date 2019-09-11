<?php

namespace Tests\Data\Entries;

use Tests\TestCase;
use Statamic\Data\Entries\GetDateFromPath;

class GetDateFromPathTest extends TestCase
{
    /** @test */
    function it_gets_the_date_from_a_path()
    {
        $this->assertEquals('2015-01-01', (new GetDateFromPath)('path/to/2015-01-01.post.md'));
        $this->assertEquals('2015-01-01-1300', (new GetDateFromPath)('path/to/2015-01-01-1300.post.md'));
        $this->assertNull((new GetDateFromPath)('path/to/post.md'));
    }
}
