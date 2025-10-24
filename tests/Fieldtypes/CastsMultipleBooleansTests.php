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
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals(
            [true, false, 'foo'],
            $field->process(['true', 'false', 'foo'])
        );

        $this->assertEquals(
            ['true', 'false', 'foo'],
            $field->preProcess([true, false, 'foo'])
        );

        $this->assertEquals(
            ['Yup', 'Nope', 'Bar'],
            $field->preProcessIndex([true, false, 'foo'])
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
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals(
            ['true', 'false', 'foo'],
            $field->process(['true', 'false', 'foo'])
        );

        $this->assertEquals(
            [true, false, 'foo'],
            $field->preProcess([true, false, 'foo'])
        );

        $this->assertEquals(
            [true, false, 'Bar'],
            $field->preProcessIndex([true, false, 'foo'])
        );
    }
}
