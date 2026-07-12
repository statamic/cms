<?php

namespace Tests\Auth\Protect;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Protect\Protectors\Fallback;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FallbackProtectorTest extends TestCase
{
    private $protector;

    public function setUp(): void
    {
        parent::setUp();

        $this->protector = new Fallback;
    }

    #[Test]
    public function it_throws_403()
    {
        try {
            $this->protector->protect();
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());

            return;
        }

        $this->fail('403 exception was not thrown.');
    }
}
