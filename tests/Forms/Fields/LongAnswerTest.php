<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\LongAnswer;
use Tests\TestCase;

class LongAnswerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new LongAnswer)->setField(new FormField('message', [
            'type' => 'long_answer',
            'placeholder' => 'Your message',
            'character_limit' => 30,
        ]));

        $this->assertEquals([
            'type' => 'textarea',
            'placeholder' => 'Your message',
            'character_limit' => 30,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new LongAnswer)->setField(new FormField('message', [
            'type' => 'long_answer',
            'placeholder' => 'Your message',
            'character_limit' => 30,
            'default' => 'David Hasselhoff',
        ]));

        $this->assertEquals([
            'type' => 'textarea',
            'placeholder' => 'Your message',
            'character_limit' => 30,
            'default' => 'David Hasselhoff',
        ], $fieldtype->toFieldArray());
    }
}
