<?php

namespace Tests\Data;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\TestCase;

class HasAugmentedDataTest extends TestCase
{
    #[Test]
    public function it_makes_an_augmented_instance()
    {
        FieldtypeRepository::shouldReceive('find')->with('test')->andReturn($fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'AUGMENTED '.$value;
            }
        });

        $thing = new class implements Augmentable
        {
            use ContainsData, HasAugmentedData;

            public function __construct()
            {
                $this->data = [
                    'foo' => 'FOO',
                    'bar' => 'BAR',
                ];
            }

            public function blueprint()
            {
                return Blueprint::make()->setContents([
                    'fields' => [
                        ['handle' => 'foo', 'field' => ['type' => 'test']],
                        ['handle' => 'baz', 'field' => ['type' => 'test']],
                    ],
                ]);
            }
        };

        $this->assertInstanceOf(Augmented::class, $thing->augmented());

        tap($thing->augmented()->get('foo'), function ($value) use ($thing, $fieldtype) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals('AUGMENTED FOO', $value->value());
            $this->assertEquals('FOO', $value->raw());
            $this->assertEquals('foo', $value->handle());
            $this->assertEquals($thing, $value->augmentable());
            $this->assertEquals($fieldtype, $value->fieldtype());
        });

        $this->assertEquals('BAR', $thing->augmentedValue('bar'));
        $this->assertEquals('BAR', $thing->augmented()->get('bar'));

        $expectedArr = [
            'foo' => new Value('FOO', 'foo', $fieldtype, $thing),
            'bar' => 'BAR',
        ];
        $this->assertEquals($expectedArr, $thing->augmented()->all()->all());
        $this->assertEquals($expectedArr, $thing->toAugmentedArray());

        $expectedSelectArr = [
            'foo' => new Value('FOO', 'foo', $fieldtype, $thing),
            'bar' => 'BAR',
        ];
        $this->assertEquals($expectedSelectArr, $thing->augmented()->select(['foo', 'bar'])->all());
        $this->assertEquals($expectedSelectArr, $thing->toAugmentedArray(['foo', 'bar']));
    }
}
