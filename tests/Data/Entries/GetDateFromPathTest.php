<?php

namespace Tests\Data\Entries;

use Statamic\Entries\GetDateFromPath;
use Tests\TestCase;

class GetDateFromPathTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider paths
     **/
    public function it_gets_the_date_from_a_path($expected, $path)
    {
        $this->assertEquals($expected, (new GetDateFromPath)($path));
    }

    public function paths()
    {
        return [
            'date' => ['2015-01-01', 'path/to/2015-01-01.post.md'],
            'time' => ['2015-01-01-1300', 'path/to/2015-01-01-1300.post.md'],
            'no date' => [null, 'path/to/post.md'],
            'no date but slug with number' => [null, 'path/to/2nd-post.md'],

            'date with id suffix' => ['2015-01-01', 'path/to/2015-01-01.post.id-suffix.md'],
            'time with id suffix' => ['2015-01-01-1300', 'path/to/2015-01-01-1300.post.id-suffix.md'],
            'no date with id suffix' => [null, 'path/to/post.id-suffix.md'],
            'no date but slug with number with id suffix' => [null, 'path/to/2nd-post.id-suffix.md'],
        ];
    }
}
