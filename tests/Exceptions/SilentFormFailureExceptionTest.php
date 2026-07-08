<?php

namespace Tests\Exceptions;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\SilentFormFailureException;
use Tests\TestCase;

class SilentFormFailureExceptionTest extends TestCase
{
    #[Test]
    public function it_keeps_the_standard_exception_constructor_for_backwards_compatibility()
    {
        $bare = new SilentFormFailureException;
        $this->assertSame('', $bare->getMessage());

        $withMessage = new SilentFormFailureException('some message', 42);
        $this->assertSame('some message', $withMessage->getMessage());
        $this->assertSame(42, $withMessage->getCode());
    }
}
