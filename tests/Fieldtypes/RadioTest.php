<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Radio;
use Tests\TestCase;

class RadioTest extends TestCase
{
    use CastsBooleansTests, HasSelectOptionsTests, LabeledValueTests;

    private function field($config)
    {
        $ft = new Radio;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
