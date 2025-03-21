<?php

namespace Tests\GraphQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Field;
use Statamic\GraphQL\Types\ArrayType;
use Statamic\GraphQL\Types\FieldType;
use Tests\TestCase;

#[Group('graphql')]
class FieldTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        GraphQL::addType(ArrayType::class);
    }

    #[Test]
    #[DataProvider('fieldProvider')]
    public function it_gets_fields(array $config, string $handle, $expected)
    {
        $type = new FieldType();
        $fields = $type->getFields();

        $field = new Field('test', $config);

        $this->assertEquals($expected, $fields[$handle]['resolve']($field));
    }

    public static function fieldProvider()
    {
        return [
            'handle' => [['type' => 'text'], 'handle', 'test'],
            'type' => [['type' => 'text'], 'type', 'text'],
            'display' => [['type' => 'text', 'display' => 'Testing'], 'display', 'Testing'],
            'instructions' => [['type' => 'text', 'instructions' => 'Instructions'], 'instructions', 'Instructions'],
            'width' => [['type' => 'text', 'width' => 75], 'width', 75],
            'width, fallback' => [['type' => 'text'], 'width', 100],
            'validate' => [
                ['type' => 'text', 'validate' => ['required', 'max:255']],
                'validate',
                ['test' => ['required', 'max:255']],
            ],
            'validate, with required key' => [
                ['type' => 'text', 'required' => true],
                'validate',
                ['test' => ['required']],
            ],
            'if' => [
                ['type' => 'text', 'if' => ['foo' => 'bar']],
                'if',
                ['foo' => 'bar'],
            ],
            'unless' => [
                ['type' => 'text', 'unless' => ['foo' => 'bar']],
                'unless',
                ['foo' => 'bar'],
            ],
            'config' => [
                [
                    'type' => 'text',
                    'input_type' => 'email',
                    'autocomplete' => 'email',
                    'other_option' => 'foo', // Not a real field, should be filtered out.
                ],
                'config',
                [
                    'input_type' => 'email',
                    'autocomplete' => 'email',
                ],
            ],
        ];
    }
}
