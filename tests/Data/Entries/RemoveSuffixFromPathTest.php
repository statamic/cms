<?php

namespace Tests\Data\Entries;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\RemoveSuffixFromPath;
use Tests\TestCase;

class RemoveSuffixFromPathTest extends TestCase
{
    #[Test]
    #[DataProvider('pathsProvider')]
    public function it_removes_the_suffix_from_a_path($expected, $path)
    {
        $this->assertEquals($expected, (new RemoveSuffixFromPath)($path));
    }

    public static function pathsProvider()
    {
        return [
            'date' => ['path/to/2015-01-01.post.md', 'path/to/2015-01-01.post.md'],
            'time' => ['path/to/2015-01-01-1300.post.md', 'path/to/2015-01-01-1300.post.md'],
            'no date' => ['path/to/post.md', 'path/to/post.md'],
            'no date but slug with number' => ['path/to/2nd-post.md', 'path/to/2nd-post.md'],

            'date with id suffix' => ['path/to/2015-01-01.post.md', 'path/to/2015-01-01.post.id-suffix.md'],
            'time with id suffix' => ['path/to/2015-01-01-1300.post.md', 'path/to/2015-01-01-1300.post.id-suffix.md'],
            'no date with id suffix' => ['path/to/post.md', 'path/to/post.id-suffix.md'],
            'no date but slug with number with id suffix' => ['path/to/2nd-post.md', 'path/to/2nd-post.id-suffix.md'],

            'date with id suffix but suffix is also in the date' => ['path/to/2015-01-01.post.md', 'path/to/2015-01-01.post.1.md'],
        ];
    }
}
