<?php

namespace Tests\CP;

use Tests\TestCase;
use Statamic\CP\Column;

class ColumnTest extends TestCase
{
    /** @test */
    function it_can_make_a_basic_column()
    {
        $column = Column::make('first_name');

        $this->assertEquals('first_name', $column->field());
        $this->assertEquals('First Name', $column->Label());
        $this->assertTrue($column->visible());
    }

    /** @test */
    function it_can_explicitly_set_data_and_serialize_to_json()
    {
        $column = Column::make()
            ->field('bars')
            ->fieldtype('grass')
            ->label('Ripped')
            ->visible(false)
            ->visibleDefault(true)
            ->defaultOrder(2);

        $json = json_decode(json_encode($column));

        $this->assertEquals('bars', $json->field);
        $this->assertEquals('grass', $json->fieldtype);
        $this->assertEquals('Ripped', $json->label);
        $this->assertFalse($json->visible);
        $this->assertTrue($json->visibleDefault);
        $this->assertEquals(2, $json->defaultOrder);
    }

    /** @test */
    function it_can_set_a_value_field()
    {
        $column = Column::make()->field('date');

        $json = json_decode(json_encode($column));
        $this->assertEquals('date', $json->field);
        $this->assertNull($json->value);

        $column->value('date_formatted');
        $json = json_decode(json_encode($column));
        $this->assertEquals('date', $json->field);
        $this->assertEquals('date_formatted', $json->value);
    }
}
