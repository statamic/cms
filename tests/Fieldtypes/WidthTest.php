<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Width;
use Tests\TestCase;

class WidthTest extends TestCase
{
    use HasSelectOptionsTests;

    private function field($config)
    {
        $ft = new Width;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }
}
