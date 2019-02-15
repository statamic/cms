<?php

namespace Tests\CP;

use Tests\TestCase;
use Statamic\CP\Column;
use Statamic\CP\Columns;

class ColumnsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->columns = new Columns([
            Column::make('first_name'),
            Column::make('last_name'),
            Column::make('email'),
        ]);
    }

    /** @test */
    function it_can_ensure_has()
    {
        $columns = $this->columns
            ->ensureHas(Column::make('date'))
            ->ensureHas(Column::make('last_name')->label('Already has last name, so this should not be used'));

        $expected = [
            'First Name',
            'Last Name',
            'Email',
            'Date',
        ];

        $this->assertEquals($expected, $columns->values()->map->label()->all());
    }

    /** @test */
    function it_can_ensure_prepended()
    {
        $columns = $this->columns
            ->ensurePrepended(Column::make('date'))
            ->ensurePrepended(Column::make('last_name')->label('Already has last name, so this should not be used'));

        $expected = [
            'Date',
            'First Name',
            'Last Name',
            'Email',
        ];

        $this->assertEquals($expected, $columns->values()->map->label()->all());
    }

    /** @test */
    function it_can_set_preferred_visibility_and_order()
    {
        $columns = $this->columns
            ->ensurePrepended(Column::make('date'))
            ->setPreferred(['first_name', 'date', 'email']);

        $expected = [
            ['field' => 'first_name', 'visible' => true],
            ['field' => 'date', 'visible' => true],
            ['field' => 'email', 'visible' => true],
            ['field' => 'last_name', 'visible' => false],
        ];

        $actual = $columns
            ->map(function ($column) {
                return ['field' => $column->field(), 'visible' => $column->visible()];
            })
            ->values()
            ->all();

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    function it_bypasses_setting_preferred_if_preference_returns_null()
    {
        $columns = $this->columns
            ->ensurePrepended(Column::make('date'))
            ->setPreferred(null);

        $expected = [
            ['field' => 'date', 'visible' => true],
            ['field' => 'first_name', 'visible' => true],
            ['field' => 'last_name', 'visible' => true],
            ['field' => 'email', 'visible' => true],
        ];

        $actual = $columns
            ->map(function ($column) {
                return ['field' => $column->field(), 'visible' => $column->visible()];
            })
            ->values()
            ->all();

        $this->assertEquals($expected, $actual);
    }

}
