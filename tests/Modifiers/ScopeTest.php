<?php

namespace Tests\Modifiers;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ScopeTest extends TestCase
{
    #[Test]
    public function it_requires_a_scope_name()
    {
        $this->expectExceptionMessage('Scope modifier requires a name.');

        $this->modify([], null);
    }

    #[Test]
    public function it_requires_an_associative_array()
    {
        $this->expectExceptionMessage('Scopes can only be added to associative or multidimensional arrays.');

        $this->modify(['one', 'two'], 'test');
    }

    #[Test]
    public function it_adds_scopes()
    {
        $arr = [
            'one' => 'foo',
            'two' => 'bar',
        ];

        $expected = [
            'one' => 'foo',
            'two' => 'bar',
            'test' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $this->modify($arr, 'test'));
    }

    #[Test]
    public function it_adds_scopes_to_collections()
    {
        $arr = collect([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $expected = [
            'one' => 'foo',
            'two' => 'bar',
            'test' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $this->modify($arr, 'test'));
    }

    #[Test]
    public function it_augments_when_adding_scope()
    {
        $arr = collect([
            'one' => new AugmentableObject(['foo' => 'bar']),
            'two' => new AugmentableObject(['foo' => 'baz']),
        ]);

        $expected = [
            'one' => ['foo' => 'bar'],
            'two' => ['foo' => 'baz'],
            'test' => [
                'one' => ['foo' => 'bar'],
                'two' => ['foo' => 'baz'],
            ],
        ];

        $this->assertEquals($expected, $this->modify($arr, 'test'));
    }

    #[Test]
    public function it_resolves_values_value_when_adding_scope()
    {
        $arr = [
            new Value(['title' => 'One']),
            new Value(['title' => 'Two']),
        ];

        $expected = [
            [
                'title' => 'One',
                'article' => [
                    'title' => 'One',
                ],
            ],
            [
                'title' => 'Two',
                'article' => [
                    'title' => 'Two',
                ],
            ],
        ];

        $this->assertSame($expected, $this->modify($arr, 'article'));
    }

    public function modify($arr, $scope)
    {
        return Modify::value($arr)->scope($scope)->fetch();
    }
}

class NonArrayableObject
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}

class ArrayableObject extends NonArrayableObject implements Arrayable
{
    public function toArray()
    {
        return $this->data;
    }
}

class AugmentableObject extends ArrayableObject implements Augmentable
{
    use HasAugmentedData;

    public function augmentedArrayData()
    {
        return $this->data;
    }

    public function blueprint()
    {
        FieldtypeRepository::shouldReceive('find')->andReturn(new class extends Fieldtype
        {
            public function augment($data)
            {
                return strtoupper($data).'!';
            }
        });

        return (new Blueprint)->setContents(['fields' => [
            ['handle' => 'one', 'field' => ['type' => 'test']],
        ]]);
    }
}
