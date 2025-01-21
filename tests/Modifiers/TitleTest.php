<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class TitleTest extends TestCase
{
    #[Test]
    public function it_converts_to_a_title(): void
    {
        $string = 'create your first PR to statamic CMS';

        $this->assertSame('Create Your First PR to Statamic CMS', $this->modify($string));
    }

    private function modify($value)
    {
        return Modify::value($value)->title()->fetch();
    }
}
