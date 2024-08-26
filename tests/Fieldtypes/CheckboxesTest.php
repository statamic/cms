<?php

namespace Tests\Fieldtypes;

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
}
