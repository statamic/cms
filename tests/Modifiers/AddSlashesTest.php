<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AddSlashesTest extends TestCase
{
    /** @test */
    public function it_adds_slashes_to_qoutes(): void
    {
        $modified = $this->modify('"I\'m not listening!" said the small, strange creature.');
        $this->assertEquals('\"I\\\'m not listening!\" said the small, strange creature.', $modified);
    }

    /** @test */
    public function it_adds_slashes_to_backslash(): void
    {
        $modified = $this->modify('Lorem ipsum dolor \sit amet, consectetur adipiscing elit');
        $this->assertEquals('Lorem ipsum dolor \\\sit amet, consectetur adipiscing elit', $modified);
    }

    /** @test */
    public function it_does_not_adds_slashes_to_parenthesis(): void
    {
        $modified = $this->modify('Lorem ipsum dolor (sit) amet, consectetur adipiscing elit');
        $this->assertEquals('Lorem ipsum dolor (sit) amet, consectetur adipiscing elit', $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->addSlashes()->fetch();
    }
}
