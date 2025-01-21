<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class RangeTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    #[Test]
    public function it_outputs_range()
    {
        $this->assertEquals('123', $this->tag('{{ range to="3" }}{{ value }}{{ /range }}'));
        $this->assertEquals('23', $this->tag('{{ range from="2" to="3" }}{{ value }}{{ /range }}'));
        $this->assertEquals('543210', $this->tag('{{ range from="5" to="0" }}{{ value }}{{ /range }}'));
    }

    #[Test]
    public function it_outputs_nothing_if_from_is_not_set_and_times_is_zero()
    {
        $this->assertEquals('', $this->tag('{{ range to="0" }}{{ value }}{{ /range }}'));
    }
}
