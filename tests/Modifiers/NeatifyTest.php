<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class NeatifyTest extends TestCase
{
    #[Test]
    public function its_kinda_neat(): void
    {
        $modified = $this->modify('Statamic');
        $this->assertEquals('Statamic is pretty neat!', $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->neatify()->fetch();
    }
}
