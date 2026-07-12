<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AtTest extends TestCase
{
    #[Test]
    public function it_returns_single_character_at_given_position(): void
    {
        $modified = $this->modify('supercalifragilisticexpialidociousx', [21]);
        $this->assertEquals('x', $modified);
    }

    #[Test]
    public function it_returns_empty_string_when_given_position_is_greater_than_word_length(): void
    {
        $modified = $this->modify('supercalifragilisticexpialidociousx', [100]);
        $this->assertEquals('', $modified);
    }

    #[Test]
    public function it_returns_first_character_when_given_position_is_zero(): void
    {
        $modified = $this->modify('supercalifragilisticexpialidociousx', [0]);
        $this->assertEquals('s', $modified);
    }

    #[Test]
    public function it_returns_character_from_end_when_given_position_is_negative(): void
    {
        $modified = $this->modify('supercalifragilisticexpialidociousx', [-3]);
        $this->assertEquals('u', $modified);
    }

    private function modify(string $value, array $params)
    {
        return Modify::value($value)->at($params)->fetch();
    }
}
