<?php

namespace Statamic\Imaging;

use League\Glide\Server;
use Statamic\Imaging\Manipulators\Glide\ImageGenerator as Generator;

/**
 * @deprecated This image generator was Glide-specific and has been moved into a properly namespaced class.
 * @see Generator
 */
class ImageGenerator
{
    private Generator $generator;

    public function __construct(Server $server)
    {
        $this->generator = new Generator($server);
    }

    public function __call($method, $args)
    {
        return $this->generator->$method(...$args);
    }

    public static function __callStatic($method, $args)
    {
        return Generator::$method(...$args);
    }
}
