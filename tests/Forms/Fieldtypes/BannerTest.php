<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Banner;
use Tests\TestCase;

class BannerTest extends TestCase
{
    #[Test]
    public function it_wraps_the_info_fieldtype()
    {
        $this->assertSame('info', Banner::fieldtype());
    }

    #[Test]
    public function it_exposes_the_info_fieldtypes_config_options()
    {
        $fields = (new Banner)->configFields();

        $this->assertSame('textarea', $fields->get('content')->type());
        $this->assertSame('select', $fields->get('state')->type());
        $this->assertSame('notice', $fields->get('state')->get('default'));
        $this->assertSame('icon', $fields->get('icon')->type());
    }

    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Banner)->setField(new FormField('notice', [
            'type' => 'banner',
            'display' => 'Important Notice',
            'content' => 'Please read this before submitting the form.',
            'state' => 'warning',
            'icon' => 'info',
        ]));

        $this->assertEquals([
            'type' => 'info',
            'hide_display' => true,
            'listable' => false,
            'display' => 'Important Notice',
            'content' => 'Please read this before submitting the form.',
            'state' => 'warning',
            'icon' => 'info',
            'instructions' => null,
            'handle' => null,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_the_notice_state()
    {
        $fieldtype = (new Banner)->setField(new FormField('notice', [
            'type' => 'banner',
            'display' => 'Important Notice',
            'content' => 'Please read this before submitting the form.',
        ]));

        $this->assertSame('notice', $fieldtype->toFieldArray()['state']);
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Banner)->setField(new FormField('notice', [
            'type' => 'banner',
            'display' => 'Important Notice',
            'content' => 'Please read this before submitting the form.',
            'width' => 50,
            'if' => ['subscribe' => 'is true'],
        ]));

        $this->assertEquals([
            'type' => 'info',
            'hide_display' => true,
            'listable' => false,
            'display' => 'Important Notice',
            'content' => 'Please read this before submitting the form.',
            'state' => 'notice',
            'icon' => null,
            'instructions' => null,
            'handle' => null,
            'width' => 50,
            'if' => ['subscribe' => 'is true'],
        ], $fieldtype->toFieldArray());
    }
}
