<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Icon;
use Tests\TestCase;

class IconTest extends TestCase
{
    #[Test]
    public function it_finds_default_icons()
    {
        $result = (string) Antlers::parse('{{ svg src="{test|raw}" }}', ['test' => new Value('add-circle', $this->fieldtype())]);

        $this->assertStringContainsString('<svg', $result);
    }

    #[Test]
    public function it_accepts_svg_strings()
    {
        $result = (string) Antlers::parse('{{ svg :src="test" class="w-4 h-4" sanitize="false" }}', ['test' => new Value('add-circle', $this->fieldtype())]);

        $this->assertStringContainsString('<svg class="w-4 h-4"', $result);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function it_validates($input, $passes)
    {
        $field = $this->fieldtype()->field();
        $messages = collect();

        try {
            Validator::validate(['test' => $input], $field->rules(), [], $field->validationAttributes());
        } catch (ValidationException $e) {
            $messages = $e->validator->errors();
        }

        if ($passes) {
            $this->assertCount(0, $messages);
        } else {
            $this->assertEquals('The test field must be a valid icon name.', $messages->first());
        }
    }

    public static function validationProvider()
    {
        return [
            'valid icon name' => [
                'add-circle',
                true,
            ],
            'html string' => [
                '<script>alert("xss")</script>',
                false,
            ],
            'svg string' => [
                '<svg></svg>',
                false,
            ],
        ];
    }

    private function fieldtype($config = [])
    {
        return (new Icon)->setField(new Field('test', array_merge(['type' => 'icon'], $config)));
    }
}
