<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\OpinionScale;
use Tests\TestCase;

class OpinionScaleTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
        ]));

        $this->assertEquals([
            'type' => 'opinion_scale',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new OpinionScale)->setField(new FormField('satisfaction', [
            'type' => 'opinion_scale',
            'display' => 'How satisfied are you?',
        ]));

        $this->assertEquals([
            'type' => 'opinion_scale',
            'display' => 'How satisfied are you?',
        ], $fieldtype->toFieldArray());
    }
}
