<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\ShortAnswer;
use Tests\TestCase;

class ShortAnswerTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new ShortAnswer)->setField(new FormField('name', [
            'type' => 'short_answer',
            'placeholder' => 'Your name',
            'character_limit' => 30,
        ]));

        $this->assertEquals([
            'type' => 'text',
            'placeholder' => 'Your name',
            'character_limit' => 30,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new ShortAnswer)->setField(new FormField('name', [
            'type' => 'short_answer',
            'placeholder' => 'Your name',
            'character_limit' => 30,
            'default' => 'David Hasselhoff',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'placeholder' => 'Your name',
            'character_limit' => 30,
            'default' => 'David Hasselhoff',
        ], $fieldtype->toFieldArray());
    }
}
