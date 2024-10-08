<?php

namespace Tests\Data\Entries;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\GetSlugFromPath;
use Tests\TestCase;

class GetSlugFromPathTest extends TestCase
{
    #[Test]
    #[DataProvider('pathsProvider')]
    public function it_gets_the_slug_from_a_path($expected, $path)
    {
        $this->assertEquals($expected, (new GetSlugFromPath)('path/to/'.$path));
    }

    public static function pathsProvider()
    {
        return [
            'date' => ['post', '2015-01-01.post.md'],
            'time' => ['post', '2015-01-01-1300.post.md'],
            'time with seconds' => ['post', 'path/to/2015-01-01-130005.post.md'],
            'no date' => ['post', 'post.md'],
            'no date but slug thats a number' => ['404', '404.md'],
            'no date but slug with number' => ['2nd-post', '2nd-post.md'],

            'date with id suffix' => ['post', '2015-01-01.post.id-suffix.md'],
            'time with id suffix' => ['post', '2015-01-01-1300.post.id-suffix.md'],
            'time with seconds and id suffix' => ['post', 'path/to/2015-01-01-130005.post.id-suffix.md'],
            'no date with id suffix' => ['post', 'post.id-suffix.md'],
            'no date but slug thats a number' => ['404', '404.md'],
            'no date but slug with number with id suffix' => ['2nd-post', '2nd-post.id-suffix.md'],
        ];
    }
}
