<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class LengthTest extends TestCase
{
    /** @test */
    public function it_returns_the_numbers_of_items_in_array(): void
    {
        $arr = [
            'Taylor Swift',
            'Left Shark',
            'Leroy Jenkins',
        ];
        $modified = $this->modify($arr);
        $this->assertSame(3, $modified);
    }

    /** @test */
    public function it_returns_the_numbers_of_chars_in_string(): void
    {
        $string = 'LEEEEROOOYYYY JEEENKINNNSS!';
        $modified = $this->modify($string);
        $this->assertSame(27, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->length()->fetch();
    }
}
