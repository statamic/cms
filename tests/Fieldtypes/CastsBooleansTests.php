<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;

trait CastsBooleansTests
{
    #[Test]
    public function it_casts_booleans_during_processing_when_enabled()
    {
        $field = $this->field([
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals(true, $field->process('true'));
        $this->assertEquals(false, $field->process('false'));
        $this->assertEquals('foo', $field->process('foo'));

        $this->assertEquals('true', $field->preProcess(true));
        $this->assertEquals('false', $field->preProcess(false));
        $this->assertEquals('foo', $field->preProcess('foo'));

        $this->assertEquals(['Yup'], $field->preProcessIndex(true));
        $this->assertEquals(['Nope'], $field->preProcessIndex(false));
        $this->assertEquals(['Bar'], $field->preProcessIndex('foo'));
    }

    #[Test]
    public function it_doesnt_cast_booleans_during_processing_when_disabled()
    {
        $field = $this->field([
            'type' => 'select',
            'cast_booleans' => false,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals('true', $field->process('true'));
        $this->assertEquals('false', $field->process('false'));
        $this->assertEquals('foo', $field->process('foo'));

        $this->assertEquals(true, $field->preProcess(true));
        $this->assertEquals(false, $field->preProcess(false));
        $this->assertEquals('foo', $field->preProcess('foo'));

        $this->assertEquals([true], $field->preProcessIndex(true));
        $this->assertEquals([false], $field->preProcessIndex(false));
        $this->assertEquals(['Bar'], $field->preProcessIndex('foo'));
    }
}
