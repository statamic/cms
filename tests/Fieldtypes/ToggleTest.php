<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Toggle;
use Tests\TestCase;

class ToggleTest extends TestCase
{
    /** @test */
    public function it_augments_to_a_boolean()
    {
        $field = (new Toggle)->setField(new Field('test', ['type' => 'toggle']));

        $this->assertFalse($field->augment(false));
        $this->assertFalse($field->augment(null));
        $this->assertTrue($field->augment(true));
    }
}
