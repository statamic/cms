<?php

namespace Tests\Data;

use Carbon\Carbon;
use Statamic\Contracts\Auth\User;
use Statamic\Fields\Value;
use Statamic\Testing\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AugmentedTestCase extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function assertAugmentedCorrectly($expectations, $augmented)
    {
        $this->assertEquals(
            collect($expectations)->keys()->sort()->values()->all(),
            $augmented->keys()
        );

        foreach ($expectations as $key => $expectation) {
            $actual = $augmented->get($key);

            if (! in_array($expectation['type'], ['string', 'bool', 'array', 'int', 'null'])) {
                $this->assertInstanceOf($expectation['type'], $actual, "Key '{$key}' is not a {$expectation['type']}");
            }

            switch ($expectation['type']) {
                case Value::class:
                    $this->assertSame($expectation['value'], $actual->value(), "Key '{$key}' does not match expected value.");
                    break;

                case Carbon::class:
                    $this->assertTrue(
                        Carbon::createFromFormat('Y-m-d H:i', $expectation['value'])->eq($actual),
                        "Key '{$key}' does not match the expected date."
                    );
                    break;

                case User::class:
                    $this->assertEquals($expectation['value'], $actual->id());
                    break;

                default:
                    if (isset($expectation['value'])) {
                        $this->assertSame(
                            $expectation['value'],
                            $actual,
                            "Key '{$key}' does not match expected value."
                        );
                    }
            }
        }
    }
}
