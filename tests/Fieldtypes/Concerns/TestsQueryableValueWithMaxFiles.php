<?php

namespace Tests\Fieldtypes\Concerns;

trait TestsQueryableValueWithMaxFiles
{
    public static function maxItemsConfigGreaterThanOne()
    {
        return [
            [[]],
            [['max_files' => 2]],
            [['max_files' => 5]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider maxItemsConfigGreaterThanOne
     **/
    public function it_normalizes_queryable_value($config)
    {
        $field = $this->fieldtype($config);

        $this->assertEquals(['123'], $field->toQueryableValue('123'));
        $this->assertEquals(['123'], $field->toQueryableValue(['123']));

        $this->assertEquals([], $this->fieldtype()->toQueryableValue([]));
        $this->assertEquals([], $this->fieldtype()->toQueryableValue(null));
    }

    /** @test */
    public function it_normalizes_queryable_value_when_max_items_is_one()
    {
        $field = $this->fieldtype(['max_files' => 1]);

        $this->assertEquals('123', $field->toQueryableValue('123'));
        $this->assertEquals('123', $field->toQueryableValue(['123']));

        $this->assertNull($field->toQueryableValue([]));
        $this->assertNull($field->toQueryableValue(null));
    }
}
