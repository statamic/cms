<?php

namespace Tests\CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Tests\TestCase;

class ColumnsTest extends TestCase
{
    private $columns;

    public function setUp(): void
    {
        parent::setUp();

        $this->columns = new Columns([
            Column::make('first_name'),
            Column::make('last_name'),
            Column::make('email'),
        ]);
    }

    #[Test]
    public function it_can_ensure_has()
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

    #[Test]
    public function it_can_ensure_prepended()
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

    #[Test]
    public function it_can_set_preferred_visibility_and_order()
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

    #[Test]
    public function it_bypasses_setting_preferred_if_preference_returns_null()
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

    #[Test]
    public function it_can_reject_unlisted_columns()
    {
        $columns = $this->columns
            ->ensureHas(Column::make('bard')->listable(false))
            ->ensureHas(Column::make('authors')->visible(false))
            ->rejectUnlisted();

        $expected = [
            'First Name',
            'Last Name',
            'Email',
            'Authors',
        ];

        $this->assertEquals($expected, $columns->values()->map->label()->all());
    }
}
