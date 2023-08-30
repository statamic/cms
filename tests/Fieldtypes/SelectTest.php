<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Select;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use CastsBooleansTests, CastsMultipleBooleansTests, LabeledValueTests, MultipleLabeledValueTests;

    private function field($config)
    {
        $ft = new Select;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
