<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class InArrayTest extends TestCase
{
    /** @test */
    public function it_checks_if_an_array_contains_a_specific_value(): void
    {
        $input = [
            'eggs',
            'flour',
            'beef jerky',
        ];

        $context = ['want' => 'eggs'];

        $modified = $this->modify($input, ['flour'], $context);
        $this->assertTrue($modified);

        $modified = $this->modify($input, ['want'], $context);
        $this->assertTrue($modified);

        $modified = $this->modify($input, ['eggs', 'flour'], $context);
        $this->assertTrue($modified);

        $modified = $this->modify($input, ['beef jerky'], $context);
        $this->assertTrue($modified);

        $modified = $this->modify($input, ['milk'], $context);
        $this->assertFalse($modified);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->inArray($params)->fetch();
    }
}
