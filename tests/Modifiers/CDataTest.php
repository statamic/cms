<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CDataTest extends TestCase
{
    #[Test]
    public function it_wraps_the_string_in_cdata_tags(): void
    {
        $modified = $this->modify('My Very Own Podcast');
        $this->assertEquals('<![CDATA[My Very Own Podcast]]>', $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->cdata()->fetch();
    }
}
