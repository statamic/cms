<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ObfuscateTest extends TestCase
{
    #[Test]
    #[DataProvider('seedProvider')]
    public function it_obfuscates_strings($seed, $value, $expected)
    {
        mt_srand($seed); // make rand predictable for testing.

        $this->assertEquals($expected, $this->modify($value));

        srand(); // reset to not affect other tests.
    }

    public static function seedProvider()
    {
        return [
            'A, case 1' => [1, 'A', '&#x41;'],
            'A, case 2' => [2, 'A', '&#65;'],
            'A, case 3' => [5, 'A', 'A'],

            'é, case 1' => [1, 'é', '&#xe9;'],
            'é, case 2' => [2, 'é', '&#233;'],
            'é, case 3' => [5, 'é', 'é'],

            '🐘, case 1' => [1, '🐘', '&#x1f418;'],
            '🐘, case 2' => [2, '🐘', '&#128024;'],
            '🐘, case 3' => [5, '🐘', '🐘'],
        ];
    }

    private function modify($value)
    {
        return Modify::value($value)->obfuscate()->fetch();
    }
}
