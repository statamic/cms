<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Toggle;
use Tests\TestCase;

class ToggleTest extends TestCase
{
    #[Test]
    public function it_augments_to_a_boolean()
    {
        $field = (new Toggle)->setField(new Field('test', ['type' => 'toggle']));

        $this->assertFalse($field->augment(false));
        $this->assertFalse($field->augment(null));
        $this->assertTrue($field->augment(true));
    }

    #[Test]
    public function it_processes_to_a_boolean_only_when_value_is_actually_set_or_submitted()
    {
        $field = (new Toggle)->setField(new Field('test', ['type' => 'toggle']));

        $this->assertTrue($field->process(true));
        $this->assertFalse($field->process(false));
        $this->assertNull($field->process(null));
    }

    #[Test]
    public function queryable_value_is_a_boolean()
    {
        $field = (new Toggle)->setField(new Field('test', ['type' => 'toggle']));

        $this->assertTrue($field->toQueryableValue(true));
        $this->assertFalse($field->toQueryableValue(false));
        $this->assertFalse($field->toQueryableValue(null));
    }
}
