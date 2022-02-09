<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FluentModifyTest extends TestCase
{
    /** @test */
    public function it_handles_params_fluently()
    {
        $result = Modify::value("i love nacho libre, it's the besss")->upper()->ensureRight('!!!');

        $this->assertInstanceOf(Modify::class, $result);
        $this->assertEquals("I LOVE NACHO LIBRE, IT'S THE BESSS!!!", (string) $result);
    }

    /** @test */
    public function it_can_explicitly_fetch_result()
    {
        $result = Modify::value("i love nacho libre, it's the besss")->upper()->ensureRight('!!!')->fetch();

        $this->assertTrue(is_string($result));
        $this->assertEquals("I LOVE NACHO LIBRE, IT'S THE BESSS!!!", $result);
    }
}
