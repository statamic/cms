<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Values;
use Statamic\Modifiers\Modifier;
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
