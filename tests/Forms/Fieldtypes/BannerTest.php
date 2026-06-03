<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Banner;
use Tests\TestCase;

class BannerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Banner)->setField(new FormField('notice', [
            'type' => 'form_banner',
        ]));

        $this->assertEquals([
            'type' => 'form_banner',
            'hide_display' => true,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Banner)->setField(new FormField('notice', [
            'type' => 'form_banner',
            'display' => 'Important Notice',
        ]));

        $this->assertEquals([
            'type' => 'form_banner',
            'hide_display' => true,
            'display' => 'Important Notice',
        ], $fieldtype->toFieldArray());
    }
}
