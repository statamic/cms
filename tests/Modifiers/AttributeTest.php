<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    #[Test]
    #[DataProvider('attributeProvider')]
    public function it_converts_to_attribute($value, $expected)
    {
        $this->assertEquals($expected, $this->modify($value, 'foo'));
    }

    public static function attributeProvider()
    {
        return [
            'string' => ['bar baz', ' foo="bar baz"'],
            'entities' => ['{<!&>}', ' foo="{&lt;!&amp;&gt;}"'],
            'integer' => [1, ' foo="1"'],
            'integer > 1' => [2, ' foo="2"'],
            'negative integer' => [-1, ' foo="-1"'],
            'float' => [1.5, ' foo="1.5"'],
            'empty string' => ['', ''],
            'true' => [true, ' foo'],
            'false' => [false, ''],
            'array' => [['one' => ['two' => 'three']], ' foo="{&quot;one&quot;:{&quot;two&quot;:&quot;three&quot;}}"'],
            'empty array' => [[], ''],
            'collection' => [collect(['one' => 'two']), ' foo="{&quot;one&quot;:&quot;two&quot;}"'],
            'empty collection' => [collect(), ''],
            'object with __toString' => [new AttributeTestStringable, ' foo="Test"'],
        ];
    }

    #[Test]
    public function it_throws_exception_without_argument()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Attribute name is required.');

        $this->modify('value', null);
    }

    #[Test]
    public function it_throws_exception_when_value_is_an_object_without_toString_method()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Object of class Tests\Modifiers\AttributeTestNotStringable could not be converted to string');

        $this->modify(new AttributeTestNotStringable, 'foo');
    }

    private function modify($value, $attribute)
    {
        return Modify::value($value)->attribute($attribute)->fetch();
    }
}

class AttributeTestStringable
{
    public function __toString()
    {
        return 'Test';
    }
}

class AttributeTestNotStringable
{
}
