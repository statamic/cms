<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;

trait CastsMultipleBooleansTests
{
    #[Test]
    public function it_casts_multiple_booleans_during_processing_when_enabled()
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals(
            [true, false, null, 'foo'],
            $field->process(['true', 'false', 'null', 'foo'])
        );

        $this->assertEquals(
            ['true', 'false', 'null', 'foo'],
            $field->preProcess([true, false, null, 'foo'])
        );

        $this->assertEquals([], $field->preProcess(null));
        $this->assertEquals(['null'], $field->preProcess([null]));

        $this->assertEquals(
            ['Yup', 'Nope', 'Dunno', 'Bar'],
            $field->preProcessIndex([true, false, null, 'foo'])
        );
    }

    #[Test]
    public function it_doesnt_cast_multiple_booleans_during_processing_when_disabled()
    {
        $field = $this->field([
            'type' => 'select',
            'multiple' => true,
            'cast_booleans' => false,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals(
            ['true', 'false', 'null', 'foo'],
            $field->process(['true', 'false', 'null', 'foo'])
        );

        $this->assertEquals(
            [true, false, null, 'foo'],
            $field->preProcess([true, false, null, 'foo'])
        );

        $this->assertEquals(
            [true, false, null, 'Bar'],
            $field->preProcessIndex([true, false, null, 'foo'])
        );
    }
}
