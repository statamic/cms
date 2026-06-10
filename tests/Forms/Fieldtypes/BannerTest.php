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
            'display' => 'Important Notice',
            'text' => 'Please read this before submitting the form.',
            'icon' => 'info',
        ]));

        $this->assertEquals([
            'type' => 'form_banner',
            'display' => 'Important Notice',
            'text' => 'Please read this before submitting the form.',
            'hide_display' => true,
            'icon' => 'info',
        ], $fieldtype->toFieldArray());
    }
}
