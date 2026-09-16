<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\ImageChoice as ImageChoiceFieldtype;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\ImageChoice as ImageChoiceFormFieldtype;
use Tests\TestCase;

class ImageChoiceTest extends TestCase
{
    #[Test]
    public function it_normalizes_options()
    {
        $fieldtype = (new ImageChoiceFieldtype)->setField(new Field('mood', [
            'type' => 'image_choice',
            'options' => [
                ['key' => 'happy', 'label' => 'Happy', 'image' => 'https://example.com/happy.jpg'],
                ['key' => 'sad', 'label' => 'Sad', 'image' => '/images/sad.jpg'],
            ],
        ]));

        $this->assertEquals([
            'options' => [
                ['key' => 'happy', 'label' => 'Happy', 'image' => 'https://example.com/happy.jpg', 'letter' => 'A'],
                ['key' => 'sad', 'label' => 'Sad', 'image' => '/images/sad.jpg', 'letter' => 'B'],
            ],
        ], $fieldtype->preload());
    }

    #[Test]
    public function it_rejects_empty_options()
    {
        $fieldtype = (new ImageChoiceFieldtype)->setField(new Field('mood', [
            'type' => 'image_choice',
            'options' => [
                ['key' => 'valid', 'label' => 'Valid'],
                ['key' => '', 'label' => 'No key'],
                ['key' => null, 'label' => 'Null key'],
                ['hidden' => true, 'key' => 'hidden'],
            ],
        ]));

        $this->assertEquals([
            'options' => [
                ['key' => 'valid', 'label' => 'Valid', 'image' => null, 'letter' => 'A'],
            ],
        ], $fieldtype->preload());
    }

    #[Test]
    public function it_uses_key_as_label_fallback()
    {
        $fieldtype = (new ImageChoiceFieldtype)->setField(new Field('mood', [
            'type' => 'image_choice',
            'options' => [
                ['key' => 'option_one'],
            ],
        ]));

        $options = $fieldtype->preload()['options'];

        $this->assertEquals('option_one', $options[0]['label']);
    }

    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new ImageChoiceFormFieldtype)->setField(new FormField('mood', [
            'type' => 'image_choice',
        ]));

        $array = $fieldtype->toFieldArray();

        $this->assertEquals('image_choice', $array['type']);
        $this->assertFalse($array['multiple']);
        $this->assertSame(3, $array['columns']);
        $this->assertSame('16/9', $array['aspect_ratio']);
        $this->assertSame(3, $array['gap']);
    }

    #[Test]
    public function it_clamps_columns_between_1_and_4()
    {
        $fieldtype = (new ImageChoiceFormFieldtype)->setField(new FormField('mood', [
            'type' => 'image_choice',
            'columns' => 6,
        ]));

        $this->assertSame(4, $fieldtype->toFieldArray()['columns']);

        $fieldtype = (new ImageChoiceFormFieldtype)->setField(new FormField('mood', [
            'type' => 'image_choice',
            'columns' => 0,
        ]));

        $this->assertSame(1, $fieldtype->toFieldArray()['columns']);
    }

    #[Test]
    public function it_normalizes_aspect_ratio()
    {
        $fieldtype = (new ImageChoiceFormFieldtype)->setField(new FormField('mood', [
            'type' => 'image_choice',
            'aspect_ratio' => '4/3',
        ]));

        $this->assertSame('4/3', $fieldtype->toFieldArray()['aspect_ratio']);

        $invalid = (new ImageChoiceFormFieldtype)->setField(new FormField('mood', [
            'type' => 'image_choice',
            'aspect_ratio' => '99/99',
        ]));

        $this->assertSame('16/9', $invalid->toFieldArray()['aspect_ratio']);
    }
}
