<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\ImageChoice;
use Tests\TestCase;

class ImageChoiceTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_defaults()
    {
        $fieldtype = (new ImageChoice)->setField(new FormField('mood', [
            'type' => 'image_choice',
        ]));

        $this->assertEquals([
            'type' => 'image_choice',
            'image_options' => [],
            'multiple' => false,
            'columns' => 3,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_normalizes_options_and_clamps_columns()
    {
        $fieldtype = (new ImageChoice)->setField(new FormField('mood', [
            'type' => 'image_choice',
            'display' => 'Pick a vibe',
            'multiple' => true,
            'columns' => 6,
            'options' => [
                ['key' => 'calm', 'label' => 'Calm', 'image' => 'https://example.com/calm.jpg'],
                ['key' => '', 'label' => 'Skip me'],
                ['hidden' => true, 'key' => 'hidden'],
            ],
        ]));

        $array = $fieldtype->toFieldArray();

        $this->assertEquals('Pick a vibe', $array['display']);
        $this->assertTrue($array['multiple']);
        $this->assertSame(4, $array['columns']);
        $this->assertEquals([
            [
                'key' => 'calm',
                'label' => 'Calm',
                'image' => 'https://example.com/calm.jpg',
                'letter' => 'A',
            ],
        ], $array['image_options']);
    }

    #[Test]
    public function it_preloads_normalized_options()
    {
        $fieldtype = (new ImageChoice)->setField(new FormField('mood', [
            'type' => 'image_choice',
            'options' => [
                ['key' => 'fun', 'label' => 'Fun', 'image' => '/assets/fun.jpg'],
            ],
        ]));

        $this->assertEquals([
            'options' => [
                [
                    'key' => 'fun',
                    'label' => 'Fun',
                    'image' => '/assets/fun.jpg',
                    'letter' => 'A',
                ],
            ],
        ], $fieldtype->preload());
    }
}
