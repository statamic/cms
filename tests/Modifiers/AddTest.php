<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Integer;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AddTest extends TestCase
{
    #[Test]
    public function it_adds_a_number(): void
    {
        $modified = $this->modify(5, [5], []);
        $this->assertEquals(10, $modified);
    }

    #[Test]
    public function it_adds_a_variable(): void
    {
        $context = [
            'magazines' => new Value(10, 'magazines', new Integer()),
        ];
        $modified = $this->modify(5, ['magazines'], $context);
        $this->assertEquals(15, $modified);
    }

    private function modify(int $value, array $params, $context)
    {
        return Modify::value($value)->context($context)->add($params)->fetch();
    }
}
