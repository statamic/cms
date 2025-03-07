<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Checkboxes;
use Tests\TestCase;

class CheckboxesTest extends TestCase
{
    use CastsMultipleBooleansTests, HasSelectOptionsTests, MultipleLabeledValueTests;

    private function field($config)
    {
        $ft = new Checkboxes;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }

    #[Test]
    public function it_filters_out_nulls()
    {
        $this->assertSame(['foo', 'bar'], $this->field([])->process(['foo', null, 'bar']));
        $this->assertSame(['foo', 'bar'], $this->field([])->preProcessValidatable(['foo', null, 'bar']));
    }
}
