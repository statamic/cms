<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Values;
use Statamic\Modifiers\Modifier;
use Statamic\Modifiers\ModifierException;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FluentModifyTest extends TestCase
{
    #[Test]
    public function it_handles_params_fluently()
    {
        $result = Modify::value("i love nacho libre, it's the besss")->upper()->ensureRight('!!!');

        $this->assertInstanceOf(Modify::class, $result);
        $this->assertEquals("I LOVE NACHO LIBRE, IT'S THE BESSS!!!", (string) $result);
    }

    #[Test]
    public function it_can_explicitly_fetch_result()
    {
        $result = Modify::value("i love nacho libre, it's the besss")->upper()->ensureRight('!!!')->fetch();

        $this->assertTrue(is_string($result));
        $this->assertEquals("I LOVE NACHO LIBRE, IT'S THE BESSS!!!", $result);
    }

    #[Test]
    public function passing_a_values_instance_into_it_will_not_convert_it_to_an_array()
    {
        $values = new Values(['foo' => 'bar']);

        $result = Modify::value($values)->fetch();

        $this->assertSame($values, $result);
    }

    #[Test]
    #[DataProvider('scalarValues')]
    public function it_casts_scalar_and_null_values_to_string($value, $expected)
    {
        $this->assertSame($expected, (string) Modify::value($value));
    }

    public static function scalarValues()
    {
        return [
            'integer zero' => [0, '0'],
            'positive integer' => [42, '42'],
            'negative integer' => [-7, '-7'],
            'float' => [1.5, '1.5'],
            'true' => [true, '1'],
            'false' => [false, ''],
            'null' => [null, ''],
            'string' => ['hello', 'hello'],
        ];
    }

    #[Test]
    public function it_casts_a_modified_integer_chain_result_to_string()
    {
        $this->assertSame('5', (string) Modify::value(0)->add(5));
    }

    #[Test]
    public function it_throws_when_casting_an_array_to_string()
    {
        $this->expectException(ModifierException::class);
        $this->expectExceptionMessage('Attempted to access modified value as a string, but encountered [array]');

        (string) Modify::value(['foo' => 'bar']);
    }

    #[Test]
    public function it_throws_modifier_exception_when_iterating_over_a_scalar()
    {
        $this->expectException(ModifierException::class);
        $this->expectExceptionMessage('Attempted to access modified value as an array, but encountered [int]');

        iterator_to_array(Modify::value(42));
    }

    #[Test]
    public function values_instances_get_converted_to_an_array_when_passing_to_a_modifier()
    {
        (new class extends Modifier
        {
            public static $handle = 'to_values';

            public function index($value)
            {
                return new Values($value);
            }
        })::register();

        $result = Modify::value(['foo' => 'bar'])->toValues()->typeOf()->fetch();

        $this->assertEquals('array', $result);
    }
}
