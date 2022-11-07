<?php

namespace Tests\Data;

use Carbon\Carbon;
use Statamic\Contracts\Auth\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AugmentedTestCase extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function assertAugmentedCorrectly($expectations, $augmented)
    {
        $this->assertAllKeysAreAugmented($expectations, $augmented);
        $this->assertAugmentedAsExpected($expectations, $augmented);
    }

    protected function assertSubsetAugmentedCorrectly($expectations, $augmented)
    {
        $this->assertAugmentedAsExpected($expectations, $augmented);
    }

    private function assertAllKeysAreAugmented($expectations, $augmented)
    {
        $this->assertEquals(
            collect($expectations)->keys()->sort()->values()->all(),
            $augmented->keys()
        );
    }

    private function assertAugmentedAsExpected($expectations, $augmented)
    {
        foreach ($expectations as $key => $expectation) {
            $valueInstance = $augmented->get($key);
            $actual = $valueInstance->value();

            if (! in_array($expectation['type'], ['string', 'bool', 'array', 'int', 'null'])) {
                $this->assertInstanceOf($expectation['type'], $actual, "Key '{$key}' is not a {$expectation['type']}");
            }

            if (isset($expectation['fieldtype'])) {
                $this->assertEquals(
                    $expectation['fieldtype'],
                    $valueInstance->fieldtype()->handle(),
                    "Key '{$key}' does not have the expected fieldtype."
                );
            }

            switch ($expectation['type']) {
                case Carbon::class:
                    $this->assertTrue(
                        Carbon::createFromFormat(strlen($expectation['value']) === 19 ? 'Y-m-d H:i:s' : 'Y-m-d H:i', $expectation['value'])->eq($actual),
                        "Key '{$key}' does not match the expected date."
                    );
                    break;

                case User::class:
                    $this->assertEquals($expectation['value'], $actual->id());
                    break;

                default:
                    if (array_key_exists('value', $expectation)) {
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
