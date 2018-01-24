<?php

namespace Tests\Tags;

use Statamic\API\File;
use Tests\TestCase;
use Statamic\API\Parse;

class PartialTagsTest extends TestCase
{
    use PartialTests;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function partialTag($src, $params = '')
    {
        return $this->tag("{{ partial:{$src} $params }}");
    }
}
