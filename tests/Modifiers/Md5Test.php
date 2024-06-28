<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class Md5Test extends TestCase
{
    #[Test]
    public function it_creates_an_md5_hash_from_value(): void
    {
        $modified = $this->modify('hello');
        $this->assertEquals('5d41402abc4b2a76b9719d911017c592', $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->md5()->fetch();
    }
}
