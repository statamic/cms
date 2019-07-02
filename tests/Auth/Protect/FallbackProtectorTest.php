<?php

namespace Tests\Auth\Protect;

use Tests\TestCase;
use Statamic\Auth\Protect\Protectors\Fallback;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FallbackProtectorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->protector = new Fallback;
    }

    /** @test */
    function it_throws_403()
    {
        try {
            $this->protector->protect();
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getCode());
            return;
        }

        $this->fail('403 exception was not thrown.');
    }
}
