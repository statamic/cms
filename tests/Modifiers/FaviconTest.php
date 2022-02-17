<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    /** @test */
    public function it_builds_an_favicon_link_from_valid_url(): void
    {
        $input = '/assets/img/favicon.png';
        $expected = '<link rel="shortcut icon" type="image/x-icon" href="/assets/img/favicon.png">';

        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->favicon()->fetch();
    }
}
