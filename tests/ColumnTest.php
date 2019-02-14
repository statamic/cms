<?php

namespace Tests;

use Tests\TestCase;
use Statamic\CP\Column;

class ColumnTest extends TestCase
{
    /** @test */
    function it_can_make_a_basic_column()
    {
        $column = Column::make('description');

        $this->assertEquals('description', $column->handle());
        $this->assertTrue($column->visible());
    }

    /** @test */
    function it_can_explicitly_set_data_and_serialize_to_json()
    {
        $column = Column::make()
            ->handle('bars')
            ->label('Ripped')
            ->visible(false);

        $json = json_decode(json_encode($column));

        $this->assertEquals('bars', $json->handle);
        $this->assertEquals('Ripped', $json->label);
        $this->assertFalse($json->visible);
    }

    /** @test */
    function it_automatically_infers_default_label_when_serializing_to_json()
    {
        $column = Column::make('description');

        $json = json_decode(json_encode($column));

        $this->assertEquals('Description', $json->label);
    }
}
