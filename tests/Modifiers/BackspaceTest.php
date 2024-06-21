<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class BackspaceTest extends TestCase
{
    #[Test]
    public function it_removes_1_char_from_the_end(): void
    {
        $modified = $this->modify('super', [1]);
        $this->assertEquals('supe', $modified);
    }

    #[Test]
    public function it_removes_29_chars_from_the_end(): void
    {
        $modified = $this->modify('supercalifragilisticexpialidocious', [29]);
        $this->assertEquals('super', $modified);
    }

    #[Test]
    public function it_removes_more_chars_then_word_length_returns_empty_string(): void
    {
        $modified = $this->modify('super', [29]);
        $this->assertEquals('', $modified);
    }

    #[Test]
    public function it_removes_no_chars(): void
    {
        $modified = $this->modify('super', []);
        $this->assertEquals('super', $modified);
    }

    #[Test]
    /**
     * @todo This originates from substr(). Is this the intended behaviour for the modifier?
     */
    public function it_returns_empty_string_when_passing_zero(): void
    {
        $modified = $this->modify('super', [0]);
        $this->assertEquals('', $modified);
    }

    private function modify(string $value, array $params)
    {
        return Modify::value($value)->backspace($params)->fetch();
    }
}
