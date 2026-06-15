<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Group;
use Tests\TestCase;

class GroupTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'display' => 'My Group',
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ],
        ]));

        $this->assertEquals([
            'type' => 'group',
            'display' => 'My Group',
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ],
        ], $fieldtype->toFieldArray());
    }
}
