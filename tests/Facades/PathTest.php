<?php

namespace Tests\Facades;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Tests\TestCase;

class PathTest extends TestCase
{
    #[Test]
    public function makes_paths_relative()
    {
        $this->assertEquals('something', Path::makeRelative(base_path('something')));
    }

    #[Test]
    #[DataProvider('extensionProvider')]
    public function it_gets_the_extension($path, $extension)
    {
        $this->assertSame($extension, Path::extension($path));
    }

    public static function extensionProvider()
    {
        return [
            'with extension' => ['path.ext', 'ext'],
            'without extension' => ['path', null],
            'url with query string' => ['http://path.ext?query=string', 'ext'],
            'instagram url' => ['https://scontent-ber1-1.cdninstagram.com/v/t51.29350-15/244716575_194960056047466_7638029823774100705_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=8ae9d6&_nc_ohc=SrkqF0twUlgAX_hLCL9&_nc_ht=scontent-ber1-1.cdninstagram.com&edm=ANo9K5cEAAAA&oh=00_AT-rO70m75BJ45PemLytAp0zC5Dhpr_asbdPKUdKKCTJkA&oe=62962256', 'jpg'], // see #6105
        ];
    }
}
