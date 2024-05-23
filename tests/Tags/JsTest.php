<?php

namespace Tests\Tags;

use Statamic\Facades\Parse;
use Tests\TestCase;

class JsTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_outputs_javascript()
    {
        $data = [
            'us_states' => [
                'AL' => 'Alabama',
                'AK' => 'Alaska',
            ],
        ];

        $this->assertEquals(
            '<script>const us_states = {"AL":"Alabama","AK":"Alaska"};</script>',
            $this->tag('{{ js:us_states }}', $data),
        );
        $this->assertEquals(
            '<script>const states = {"AL":"Alabama","AK":"Alaska"};</script>',
            $this->tag('{{ js name="states" :from="us_states" }}', $data),
        );
        $this->assertEquals(
            'let us_states = {"AL":"Alabama","AK":"Alaska"};',
            $this->tag('{{ js:us_states script="false" const="false" }}', $data),
        );
    }
}
