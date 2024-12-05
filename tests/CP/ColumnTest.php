<?php

namespace Tests\CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Column;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    #[Test]
    public function it_can_make_a_basic_column()
    {
        $column = Column::make('first_name');

        $this->assertEquals('first_name', $column->field());
        $this->assertEquals('First Name', $column->Label());
        $this->assertTrue($column->visible());
    }

    #[Test]
    public function it_can_explicitly_set_data_and_serialize_to_json()
    {
        $column = Column::make()
            ->field('bars')
            ->fieldtype('grass')
            ->label('Ripped')
            ->visible(false)
            ->defaultVisibility(true)
            ->defaultOrder(2)
            ->numeric(true);

        $json = json_decode(json_encode($column));

        $this->assertEquals('bars', $json->field);
        $this->assertEquals('grass', $json->fieldtype);
        $this->assertEquals('Ripped', $json->label);
        $this->assertFalse($json->visible);
        $this->assertTrue($json->defaultVisibility);
        $this->assertEquals(2, $json->defaultOrder);
        $this->assertEquals(true, $json->numeric);
    }

    #[Test]
    public function it_can_set_a_value_field()
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
