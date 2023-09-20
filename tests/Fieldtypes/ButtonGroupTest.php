<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\ButtonGroup;
use Tests\TestCase;

class ButtonGroupTest extends TestCase
{
    use CastsBooleansTests, LabeledValueTests;

    private function field($config)
    {
        $ft = new ButtonGroup;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
