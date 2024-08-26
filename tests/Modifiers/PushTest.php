<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class PushTest extends TestCase
{
    #[Test]
    public function it_pushes_an_item_to_an_array(): void
    {
        $checklist = [
            'zebra',
            'hippo',
        ];

        $expected = [
            'zebra',
            'hippo',
            'hyena',
        ];

        $modified = $this->modify($checklist, ['hyena']);

        $this->assertEquals($expected, $modified);
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->push($params)->fetch();
    }
}
