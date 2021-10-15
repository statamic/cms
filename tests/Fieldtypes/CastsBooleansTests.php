<?php

namespace Tests\Fieldtypes;

trait CastsBooleansTests
{
    /** @test */
    public function it_casts_booleans_during_processing_when_enabled()
    {
        $field = $this->field([
            'type' => 'select',
            'cast_booleans' => true,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals(true, $field->process('true'));
        $this->assertEquals(false, $field->process('false'));
        $this->assertEquals(null, $field->process('null'));
        $this->assertEquals('foo', $field->process('foo'));

        $this->assertEquals('true', $field->preProcess(true));
        $this->assertEquals('false', $field->preProcess(false));
        $this->assertEquals('null', $field->preProcess(null));
        $this->assertEquals('foo', $field->preProcess('foo'));
    }

    /** @test */
    public function it_doesnt_cast_booleans_during_processing_when_disabled()
    {
        $field = $this->field([
            'type' => 'select',
            'cast_booleans' => false,
            'options' => [
                'true' => 'Yup',
                'false' => 'Nope',
                'null' => 'Dunno',
                'foo' => 'Bar',
            ],
        ]);

        $this->assertEquals('true', $field->process('true'));
        $this->assertEquals('false', $field->process('false'));
        $this->assertEquals('null', $field->process('null'));
        $this->assertEquals('foo', $field->process('foo'));

        $this->assertEquals(true, $field->preProcess(true));
        $this->assertEquals(false, $field->preProcess(false));
        $this->assertEquals(null, $field->preProcess(null));
        $this->assertEquals('foo', $field->preProcess('foo'));
    }
}
