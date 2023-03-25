<?php

namespace Tests\Data\Entries;

use Statamic\Entries\GetSuffixFromPath;
use Tests\TestCase;

class GetSuffixFromPathTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider paths
     **/
    public function it_gets_the_suffix_from_a_path($expected, $path)
    {
        $this->assertEquals($expected, (new GetSuffixFromPath)($path));
    }

    public function paths()
    {
        return [
            'date' => [null, 'path/to/2015-01-01.post.md'],
            'time' => [null, 'path/to/2015-01-01-1300.post.md'],
            'no date' => [null, 'path/to/post.md'],
            'no date but slug with number' => [null, 'path/to/2nd-post.md'],

            'date with id suffix' => ['id-suffix', 'path/to/2015-01-01.post.id-suffix.md'],
            'time with id suffix' => ['id-suffix', 'path/to/2015-01-01-1300.post.id-suffix.md'],
            'no date with id suffix' => ['id-suffix', 'path/to/post.id-suffix.md'],
            'no date but slug with number with id suffix' => ['id-suffix', 'path/to/2nd-post.id-suffix.md'],
        ];
    }
}
